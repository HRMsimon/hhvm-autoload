<?hh // strict

namespace FredEmmott\AutoloadMap;

final class ScannerTest extends \PHPUnit_Framework_TestCase {
  const string FIXTURES = __DIR__.'/fixtures';
  const string HH_ONLY_SRC = self::FIXTURES.'/hh-only/src';
  const string FIXTURES_PREFIX =
    "FredEmmott\\AutoloadMap\\TestFixtures\\";

  public function testHHOnly(): void {
    $map = Scanner::fromTree(self::HH_ONLY_SRC)->getAutoloadMap();

    $this->assertMapMatches(
      [
        'ExampleClass' => 'class.php',
        'ExampleEnum' => 'enum.php',
        'xhp_example__xhp_class' => 'xhp_class.php',
      ],
      $map['class'],
    );

    $this->assertMapMatches(
      [ 'example_function' => 'function.php' ],
      $map['function'],
    );

    $this->assertMapMatches(
      [
        'ExampleType' => 'type.php',
        'ExampleNewtype' => 'newtype.php',
      ],
      $map['type'],
    );

    $this->assertMapMatches(
      [
        'FREDEMMOTT_AUTOLOAD_MAP_TEST_FIXTURES_EXAMPLE_CONSTANT'
          => 'constant.php',
      ],
      $map['constant'],
    ); 
  }

  public function testFromFile(): void {
    $map = Scanner::fromFile(self::HH_ONLY_SRC.'/constant.php')
      ->getAutoloadMap();
    $this->assertEmpty($map['class']);
    $this->assertEmpty($map['function']);
    $this->assertEmpty($map['type']);
    $this->assertMapMatches(
      [
        'FREDEMMOTT_AUTOLOAD_MAP_TEST_FIXTURES_EXAMPLE_CONSTANT'
          => 'constant.php',
      ],
      $map['constant'],
    );
  }

  private function assertMapMatches(
    array<string, string> $expected,
    array<string, string> $actual,
  ): void {
    foreach ($expected as $name => $file) {
      $a = self::HH_ONLY_SRC.'/'.$file;
      $b = 
        idx($actual, strtolower(self::FIXTURES_PREFIX.$name))
        ?: idx($actual, self::FIXTURES_PREFIX.$name)
        ?: idx($actual, $name);
      
      $this->assertSame($a, $b);
    }
  }
}
