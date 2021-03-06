<?hh // strict

namespace FredEmmott\AutoloadMap;

final class RootImporter implements Builder {
  private Vector<Builder> $builders = Vector { };

  public function __construct(
    string $root,
  ) {
    $hh_importer = new HHImporter($root);
    $this->builders[] = $hh_importer;
    $config = $hh_importer->getConfig();

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
}
