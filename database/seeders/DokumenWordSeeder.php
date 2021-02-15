<?php

namespace Database\Seeders;

use App\FileWord;
use Database\Factories\DokumenWordFactory;
use DOMDocument;
use Faker\Generator as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PhpOffice\PhpWord\Exception\Exception;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;

class DokumenWordSeeder extends Seeder
{
    private Faker $faker;

    public function __construct(Faker $faker)
    {
        $this->faker = $faker;
    }

    /**
     * Run the database seeds.
     *
     * @return void
     * @throws Exception
     */
    public function run()
    {
        FileWord::query()->delete();

        DB::beginTransaction();

        DokumenWordFactory::new()
            ->count(10)
            ->create()
            ->each(function (FileWord $dokumenWord) {
                $tempDocxFilename = "temp.docx";
                $tempHTMLFilename = "temp.html";
                $docxObject = $this->getRandomWordDocument();

                $docxWriter = IOFactory::createWriter($docxObject, "Word2007");
                $docxWriter->save(__DIR__ . DIRECTORY_SEPARATOR . $tempDocxFilename);

                $htmlWriter = IOFactory::createWriter($docxObject, "HTML");
                $htmlWriter->save(__DIR__ . DIRECTORY_SEPARATOR . $tempHTMLFilename);

                $dokumenWord->update([
                    "konten_html" => $this->extractBodyContent(__DIR__ . DIRECTORY_SEPARATOR . $tempHTMLFilename)
                ]);

                $dokumenWord
                    ->addMediaFromDisk($tempDocxFilename, "seeders")
                    ->usingFileName(Str::snake($dokumenWord->nama) . ".docx")
                    ->toMediaCollection(FileWord::COLLECTION_WORD_FILE);

                unlink(__DIR__ . DIRECTORY_SEPARATOR . $tempHTMLFilename);
            });

        DB::commit();
    }

    public function getRandomWordDocument($sectionsCount = 5, $paragraphsPerSession = 5): PhpWord
    {
        $phpWord = new PhpWord();

        foreach (range(0, $sectionsCount) as $sessionCounter) {
            $section = $phpWord->addSection();
            foreach (range(0, $paragraphsPerSession) as $paragraphCounter) {
                $section->addText($this->faker->realText(rand(1, 10) * 100));
            }
        }
        return $phpWord;
    }

    /**
     * @param string $path
     * @return string
     */
    public function extractBodyContent(string $path): string
    {
        $htmlDocument = new DOMDocument();
        $htmlDocument->loadHtml(
            file_get_contents($path)
        );
        $elements = $htmlDocument->getElementsByTagName("body");
        $body = $elements->item(0);
        $tempDiv = $htmlDocument->createElement("div");
        foreach ($body->childNodes as $childNode) {
            $tempDiv->appendChild($childNode->cloneNode(true));
        }
        $htmlDocument->appendChild($tempDiv);

        return $tempDiv->C14N();
    }
}
