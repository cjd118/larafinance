<?php

namespace Tests\Unit\Support;

use App\Support\Pagination;
use Illuminate\Http\Request;
use PHPUnit\Framework\TestCase;

class PaginationTest extends TestCase
{
    public function testReturnsDefaultWhenPerPageIsAbsent(): void
    {
        $this->assertSame(50, Pagination::resolvePerPage(new Request(), 50, 100));
    }

    public function testReturnsRequestedValueWhenWithinBounds(): void
    {
        $request = new Request(['per_page' => '25']);
        $this->assertSame(25, Pagination::resolvePerPage($request, 50, 100));
    }

    public function testCapsAtMaxWhenRequestedAboveCeiling(): void
    {
        $request = new Request(['per_page' => '99999']);
        $this->assertSame(100, Pagination::resolvePerPage($request, 50, 100));
    }

    public function testFloorsToOneWhenRequestedZeroOrNegative(): void
    {
        $request = new Request(['per_page' => '0']);
        $this->assertSame(1, Pagination::resolvePerPage($request, 50, 100));

        $request = new Request(['per_page' => '-5']);
        $this->assertSame(1, Pagination::resolvePerPage($request, 50, 100));
    }

    public function testFloorsToOneWhenRequestedNonNumeric(): void
    {
        $request = new Request(['per_page' => 'abc']);
        $this->assertSame(1, Pagination::resolvePerPage($request, 50, 100));
    }
}
