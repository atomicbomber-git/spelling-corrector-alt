<?php

namespace App\Console\Commands;

use App\Support\PdfImporter;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use RecursiveDirectoryIterator;
use TextAnalysis\Tokenizers\GeneralTokenizer;

class BulkImportWordsFromPdfCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bulk-import-words {path}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Bulk import words from a directory containing PDF files.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $path = $this->argument('path');

        if (scandir($path) === false) {
            $this->error("Directory doesn't exist");
            return 1;
        }

        $iterator = new \RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(
                $path
            )
        );

        $pdfImporter = app(PdfImporter::class);

        $this->info("Importing PDFs...");

        DB::beginTransaction();

        foreach ($iterator as $file) {
            if ($file->getExtension() === "pdf") {
                $filePath = $file->getPathName();
                $this->info("Importing {$filePath}");
                $pdfImporter->import($filePath);
            }
        }

        DB::commit();

        return 0;
    }
}
