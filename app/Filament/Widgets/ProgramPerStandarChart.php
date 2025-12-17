<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class ProgramPerStandarChart extends ChartWidget
{
    protected ?string $heading = 'Jumlah Program per Standar';
    // protected int|string|array $columnSpan = 'full';    // kecil & rapi (setengah layar)
    protected static ?int $sort = 2;
    protected static bool $isLazy = true;

    protected function getType(): string
    {
        return 'bar'; // paling cocok & hemat tempat untuk data kategori
        // alternatif: 'pie', 'bar', atau 'polarArea'
    }

    protected function getData(): array
    {
        $data = DB::table('tb_program_kegiatan as pk')
            ->join('tb_uraian_program as up', 'pk.id', '=', 'up.id_program_kegiatan')
            ->join('tb_standar as s', 'up.id_standar', '=', 's.id')
            ->selectRaw('s.nama_standar as standar, COUNT(*) as jumlah')
            ->groupBy('s.id', 's.nama_standar')
            ->orderByDesc('jumlah')
            ->get();

        // Warna cantik & konsisten (bisa ditambah kalau standar > 8)
        $colors = [
            '#3B82F6', // blue
            '#10B981', // emerald
            '#F59E0B', // amber
            '#EF4444', // red
            '#8B5CF6', // violet
            '#EC4899', // pink
            '#14B8A6', // teal
            '#F97316', // orange
        ];

        return [
            'datasets' => [
                [
                    'data' => $data->pluck('jumlah')->toArray(),
                    'backgroundColor' => $data->map(fn($item, $index) => $colors[$index % count($colors)]),
                    'borderColor' => '#1F2937',
                    'borderWidth' => 2,
                    'hoverOffset' => 15,
                ],
            ],
            'labels' => $data->pluck('standar')->map(fn($nama) =>
                \Illuminate\Support\Str::limit($nama, 25, '...')
            )->toArray(),
        ];
    }

    // protected function getOptions(): array
    // {
    //     return [
    //         'responsive' => true,
    //         'maintainAspectRatio' => false,
    //         'plugins' => [
    //             'legend' => [
    //                 'position' => 'right',     // karena donut, legend di kanan lebih rapi
    //                 'labels' => [
    //                     'font' => ['size' => 11],
    //                     'padding' => 15,
    //                     'usePointStyle' => true,
    //                 ],
    //             ],
    //             'tooltip' => [
    //                 'callbacks' => [
    //                     'label' => fn($item) =>
    //                         $item->label . ': ' . $item->raw . ' program'
    //                 ]
    //             ],
    //         ],
    //     ];
    // }

    protected function getOptions(): array
    {
        return [
            'indexAxis' => 'y',
            'responsive' => true,
            'plugins' => [
                'legend' => ['display' => false], // tidak perlu legend kalau cuma 1 dataset
            ],
            'scales' => [
                'x' => ['ticks' => ['stepSize' => 1]],
            ],
        ];
    }
}