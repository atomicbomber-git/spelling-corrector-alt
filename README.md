# Spelling Corrector

## Deskripsi Data di Lookup Table
- Jumlah kata di lookup table dari kata.txt (sumber: https://raw.githubusercontent.com/agulagul/Indonesia-words/master/kata.txt) yang dimasukkan: 29.932
- Jumlah kata di lookup table dari 520 artikel jurnal JUSTIN & JEPIN: 18.803
- Jumlah kata di lookup table dari KBBI (sumber: https://github.com/andrisetiawan/lexicon/blob/master/kata_dasar_kbbi.csv): 412
- Jumlah kata di lookup table dari korpus campuran b. indonesia (sumber: Indonesian Mixed https://wortschatz.uni-leipzig.de/en/download/Indonesian): 237.341

# Lokasi di Kodingan

## Pembuatan Ngram & Isi Lookup Table dari Teks Jurnal
database/seeders/NgramAndWordSeeder.php 
baris 53 - 110

## Isi Lookup Table dari CSV KBBI
database/seeders/KbbiSeeder.php (seluruh file)

## Upload DOCX
app/Http/Controllers/FileWordController.php
baris 69 - 113

## Periksa Kamus
app/Tools/RekomendatorKoreksiEjaan.php
Baris 63

## Download Dokumen
app/Http/Controllers/FileWordDownloadController.php (seluruh file)

## Proses Perhitungan N-Gram
app/Tools/RekomendatorKoreksiEjaan.php
baris 75 - 249
