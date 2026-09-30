<?php

namespace App\Services\Pdf;

use Spatie\LaravelPdf\Facades\Pdf;

class ChromiumPdfDocument
{
    protected string $title;
    protected string $orientation; // 'L' (landscape) or 'P' (portrait)
    protected array $pages = [];
    protected int $currentPageIndex = -1;

    public function __construct(string $title = '', string $orientation = 'L')
    {
        $this->title = $title;
        $this->orientation = strtoupper($orientation);
    }

    public function SetCreator(string $creator): self { return $this; }
    public function SetAuthor(string $author): self { return $this; }
    public function SetTitle(string $title): self { $this->title = $title; return $this; }
    public function SetSubject(string $subject): self { return $this; }
    public function setRTL(bool $enable): self { return $this; }
    public function SetFont(string $family, string $style = '', ?float $size = null): self { return $this; }
    public function SetMargins(float $left, float $top, ?float $right = -1): self { return $this; }
    public function SetHeaderMargin(float $hm): self { return $this; }
    public function SetFooterMargin(float $fm): self { return $this; }
    public function SetAutoPageBreak(bool $auto, float $margin = 0): self { return $this; }
    public function setImageScale(float $scale): self { return $this; }
    public function setPrintHeader(bool $val): self { return $this; }
    public function setPrintFooter(bool $val): self { return $this; }

    public function AddPage(?string $orientation = null): void
    {
        if ($orientation) {
            $this->orientation = strtoupper($orientation);
        }
        $this->currentPageIndex++;
        $this->pages[$this->currentPageIndex] = '';
    }

    public function writeHTML(string $html, ...$args): void
    {
        if ($this->currentPageIndex < 0) {
            $this->AddPage();
        }
        $this->pages[$this->currentPageIndex] .= $html;
    }

    public function Output(string $name = 'document.pdf', string $dest = 'S'): string
    {
        $viewData = [
            'title' => $this->title,
            'pages' => !empty($this->pages) ? $this->pages : [''],
            'orientation' => $this->orientation,
        ];

        $pdf = Pdf::view('reports.pdf-wrapper', $viewData)
            ->format('a4');

        if ($this->orientation === 'P') {
            $pdf->portrait();
        } else {
            $pdf->landscape();
        }

        return $pdf->generatePdfContent();
    }
}
