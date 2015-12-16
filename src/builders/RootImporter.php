<?hh // strict

namespace FredEmmott\AutoloadMap;

final class RootImporter implements Builder {
  private Vector<Builder> $builders = Vector { };

  public function __construct(
    string $root,
    Config $config,
  ) {
    foreach ($config['roots'] as $tree) {
      if ($tree[0] !== '/') {
        $tree = $root.'/'.$tree;
      }
      $this->builders[] = Scanner::fromTree($tree);
    }

    if (!$config['includeVendor']) {
      return;
    }

    foreach (glob($root.'/vendor/*/*/composer.json') as $composer_json) {
      $this->builders[] = new ComposerImporter($composer_json, $config);
    }
  }

  public function getAutoloadMap(): AutoloadMap {
    return Merger::merge(
      $this->builders->map($builder ==> $builder->getAutoloadMap())
    );
  }

  public function getFiles(): ImmVector<string> {
    $files = Vector { };
    foreach ($this->builders as $builder) {
      $files->addAll($builder->getFiles());
    }
    return $files->toImmVector();
  }

  public static function forTree(string $root): Builder {
    $config = $root.'/hh_autoload.json';
    if (!file_exists($config)) {
      throw new Exception("%s does not exist", $config);
    }
    $config = ConfigurationLoader::fromFile($config);
    return new RootImporter($root, $config);
  }
}
