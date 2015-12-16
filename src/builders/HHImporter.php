<?hh // strict

namespace FredEmmott\AutoloadMap;

final class HHImporter implements Builder {
  private Vector<Builder> $builders = Vector { };
  private Config $config;

  public function __construct(
    string $root,
  ) {
    $config_file = $root.'/hh_autoload.json';
    $config = ConfigurationLoader::fromFile($config_file);
    $this->config = $config;

    foreach ($config['roots'] as $tree) {
      if ($tree[0] !== '/') {
        $tree = $root.'/'.$tree;
      }
      $this->builders[] = Scanner::fromTree($tree);
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

  public function getConfig(): Config {
    return $this->config;
  }
}
