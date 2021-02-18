<?php

use App\Support\StringUtil;

test("Can replace text in XML correctly", function () {

    $htmlString = /** @lang HTML */ <<<HERE
<div id="root">
    <p> Mary has a little lamb. </p>
    <p> Mary goes to the market. </p>
    <p> Mary doesn't care about John. </p>
</div>
HERE;

    $domDocument = new DOMDocument();
    $domDocument->loadHTML($htmlString);

     StringUtil::replaceTextsInXmlTreeNodes(
        "/\bMary\b/", [
            ["index" => 0, "correction" => "P"],
            ["index" => 1, "correction" => "Q"],
            ["index" => 2, "correction" => "R"],
        ],
        $domDocument,
    );

    expect($domDocument->saveHTML($domDocument->getElementById("root")))
        ->toBe(/** @lang HTML */ <<<HERE
<div id="root">
    <p> P has a little lamb. </p>
    <p> Q goes to the market. </p>
    <p> R doesn't care about John. </p>
</div>
HERE);
});