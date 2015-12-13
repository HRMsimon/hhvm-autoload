<?hh // strict

namespace FredEmmott\AutoloadMap;

final class ConfigurationLoaderTest extends \PHPUnit_Framework_TestCase {
  public function goodTestCases(
  ): array<string, array<array<string, mixed>>> {
    return [
      'fully specified' => [[
        'autoloadFilesBehavior' => AutoloadFilesBehavior::EXEC_FILES,
        'composerJsonFallback' => true,
        'roots' => ['foo/', 'bar/'],
      ]],
      'just roots' => [[
        'roots' => ['foo/', 'bar/'],
      ]],
    ];
  }

  /**
   * @dataProvider goodTestCases
   */
  public function testDataLoader(array<string, mixed> $data): void {
    $config = ConfigurationLoader::fromData($data, '/dev/null');
    $this->assertGoodConfig($data, $config);
  }

  /**
   * @dataProvider goodTestCases
   */
  public function testJSONLoader(array<string, mixed> $data): void {
    $config = ConfigurationLoader::fromJSON(
      json_encode($data),
      '/dev/null',
    );
    $this->assertGoodConfig($data, $config);
  }

  /**
   * @dataProvider goodTestCases
   */
  public function testFileLoader(array<string, mixed> $data): void {
    $fname = tempnam(sys_get_temp_dir(), 'testjson');
    try {
      file_put_contents(
        $fname,
        json_encode($data),
      );
      $config = ConfigurationLoader::fromFile($fname);
      $this->assertGoodConfig($data, $config);
    } finally {
      unlink($fname);
    }
  }

  private function assertGoodConfig(
    array<string, mixed> $data,
    Config $config,
  ): void {
    $this->assertEquals(
      $data['roots'],
      $config['roots']->toArray(),
    );

    $this->assertNotNull(
      AutoloadFilesBehavior::coerce($config['autoloadFilesBehavior'])
    );
    $this->assertTrue(is_bool($config['composerJsonFallback']));

    $config = Shapes::toArray($config); 
    foreach ($data as $key => $value) {
      if ($key === 'roots') {
        continue;
      }
      $this->assertSame($value, $config[$key]);
    }
  }
}
