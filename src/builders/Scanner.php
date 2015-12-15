<?hh // strict

namespace FredEmmott\AutoloadMap;

use FredEmmott\DefinitionFinder\BaseParser;
use FredEmmott\DefinitionFinder\FileParser;
use FredEmmott\DefinitionFinder\ScannedBase;
use FredEmmott\DefinitionFinder\TreeParser;

final class Scanner implements Builder {
  private function __construct(
    private BaseParser $parser,
  ) {
  }

  public static function fromFile(
    string $path,
  ): Scanner {
    return new Scanner(
      FileParser::FromFile($path),
    );
  }

  public static function fromData(
    string $data,
    ?string $filename = null,
  ): Scanner {
    return new Scanner(
      FileParser::FromData($data, $filename),
    );
  }

  public static function fromTree(
    string $path,
  ): Scanner {
    return new Scanner(
      TreeParser::FromPath($path),
    );
  }

  public function getAutoloadMap(): AutoloadMap {
    $classes = ((Vector { })
      ->addAll($this->parser->getClasses())
      ->addAll($this->parser->getInterfaces())
      ->addAll($this->parser->getTraits())
      ->addAll($this->parser->getEnums())
    );
    $functions = $this->parser->getFunctions();
    $types = ((Vector { })
      ->addAll($this->parser->getTypes())
      ->addAll($this->parser->getNewtypes())
    );
    $constants = $this->parser->getConstants();

    return shape(
      'class' => $this->getLowerCaseFileMap($classes),
      'function' => $this->getLowerCaseFileMap($functions),
      'type' => $this->getLowerCaseFileMap($types),
      'constant' => $this->getCasePreservedFileMap($constants),
    );
  }

  public function getFiles(): ImmVector<string> {
    return ImmVector { };
  }

  private function getCasePreservedFileMap<T as ScannedBase>(
    \ConstVector<T> $scanned,
  ): array<string, string> {
    $out = [];
    foreach ($scanned as $def) {
      $out[$def->getName()] = $def->getFileName();
    }
    return $out;
  }

  private function getLowerCaseFileMap<T as ScannedBase>(
    \ConstVector<T> $scanned,
  ): array<string, string> {
    $scanned = $this->getCasePreservedFileMap($scanned);
    $out = [];
    foreach ($scanned as $k => $v) {
      $out[strtolower($k)] = $v;
    }
    return $out;
  }
}
