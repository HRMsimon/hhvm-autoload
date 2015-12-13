<?hh // strict

namespace FredEmmott\AutoloadMap;

abstract class Exception extends \Exception {
  public function __construct(
    \HH\FormatString<\PlainSprintf> $format,
    array<mixed> ...$args
  ) {
    /* HH_FIXME[4027] - the typechecker's printf support doesn't allow
     * passing it along to something else that has validated format
     * strings */
    parent::__construct(sprintf($format, ...$args));
  }
}