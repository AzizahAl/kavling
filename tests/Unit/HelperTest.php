<?php

namespace Tests\Unit;

use App\Support\Terbilang;
use PHPUnit\Framework\TestCase;

class HelperTest extends TestCase
{
    public function test_format_rupiah_dan_persen(): void
    {
        $this->assertSame('Rp1.500.000', rupiah(1500000));
        $this->assertSame('-Rp500.000', rupiah(-500000));
        $this->assertSame('Rp49 jt', rupiah_singkat(49000000));
        $this->assertSame('Rp1,25 M', rupiah_singkat(1250000000));
        $this->assertSame('50%', persen(50, false));
        $this->assertSame('10%', persen(10, false));
        $this->assertSame('2,5%', persen(2.5, false, 2));
        $this->assertSame('25%', persen(0.25));
    }

    public function test_terbilang(): void
    {
        $this->assertSame('Dua ratus lima puluh ribu rupiah', Terbilang::rupiah(250000));
        $this->assertSame('Empat puluh sembilan juta rupiah', Terbilang::rupiah(49000000));
        $this->assertSame('Seribu seratus sebelas rupiah', Terbilang::rupiah(1111));
    }
}
