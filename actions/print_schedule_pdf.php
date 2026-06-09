<?php

session_start();

require_once "../config/db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../pages/login.php");
    exit;
}

$userId = $_SESSION["user_id"];
$now = date("Y-m-d H:i:s");

$sql = "
SELECT presentations.*
FROM personal_schedule
JOIN presentations ON personal_schedule.presentation_id = presentations.id
WHERE personal_schedule.user_id = :user_id
AND CONCAT(presentations.presentation_date, ' ', presentations.end_time) >= :now
ORDER BY presentations.presentation_date ASC, presentations.start_time ASC
";

$stmt = $pdo->prepare($sql);
$stmt->execute([
    ":user_id" => $userId,
    ":now" => $now
]);

$presentations = $stmt->fetchAll(PDO::FETCH_ASSOC);

$pdf = new SchedulePdf("Generated on " . date("Y-m-d H:i"));
$pdf->addSchedule($presentations);

$filename = "my_schedule_" . date("Y-m-d") . ".pdf";
$pdfContent = $pdf->render();

header("Content-Type: application/pdf");
header("Content-Disposition: attachment; filename=\"" . $filename . "\"");
header("Content-Length: " . strlen($pdfContent));
header("Cache-Control: private, max-age=0, must-revalidate");
header("Pragma: public");

echo $pdfContent;
exit;

class SchedulePdf
{
    private $pages = [];
    private $currentPage = -1;
    private $width = 595.28;
    private $height = 841.89;
    private $margin = 50;
    private $y = 0;
    private $subtitle;

    public function __construct($subtitle)
    {
        $this->subtitle = $subtitle;
        $this->addPage();
    }

    public function addSchedule($presentations)
    {
        if (count($presentations) === 0) {
            $this->ensureSpace(30);
            $this->text("Your schedule is currently empty.", $this->margin, $this->y, 12, false, [75, 85, 99]);
            return;
        }

        foreach ($presentations as $index => $presentation) {
            $this->addPresentation($presentation, $index + 1);
        }
    }

    public function render()
    {
        $objects = [null];
        $catalogId = $this->addObject($objects, "");
        $pagesId = $this->addObject($objects, "");
        $fontId = $this->addObject($objects, "<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>");
        $boldFontId = $this->addObject($objects, "<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold /Encoding /WinAnsiEncoding >>");
        $pageIds = [];

        foreach ($this->pages as $pageContent) {
            $contentId = $this->addObject(
                $objects,
                "<< /Length " . strlen($pageContent) . " >>\nstream\n" . $pageContent . "endstream"
            );

            $pageIds[] = $this->addObject(
                $objects,
                "<< /Type /Page /Parent " . $pagesId . " 0 R /MediaBox [0 0 " . $this->num($this->width) . " " . $this->num($this->height) . "] /Resources << /Font << /F1 " . $fontId . " 0 R /F2 " . $boldFontId . " 0 R >> >> /Contents " . $contentId . " 0 R >>"
            );
        }

        $kids = "";
        foreach ($pageIds as $pageId) {
            $kids .= $pageId . " 0 R ";
        }

        $objects[$catalogId] = "<< /Type /Catalog /Pages " . $pagesId . " 0 R >>";
        $objects[$pagesId] = "<< /Type /Pages /Kids [" . trim($kids) . "] /Count " . count($pageIds) . " >>";

        return $this->buildPdf($objects, $catalogId);
    }

    private function addPresentation($presentation, $number)
    {
        $this->ensureSpace(155);
        $this->line($this->margin, $this->y + 12, $this->width - $this->margin, $this->y + 12, [209, 213, 219]);

        foreach ($this->wrapText($number . ". " . $presentation["title"], 70) as $titleLine) {
            $this->ensureSpace(22);
            $this->text($titleLine, $this->margin, $this->y, 14, true, [17, 24, 39]);
            $this->y -= 18;
        }

        $this->writeInfoLine("Presenter", $presentation["presenter"]);
        $this->writeInfoLine("Category", $presentation["category"]);
        $this->writeInfoLine("Date", $presentation["presentation_date"]);
        $this->writeInfoLine("Time", $this->formatTime($presentation["start_time"]) . " - " . $this->formatTime($presentation["end_time"]));
        $this->writeInfoLine("Room", $presentation["room"]);
        $this->writeInfoLine("Description", $presentation["description"]);

        $this->y -= 14;
    }

    private function writeInfoLine($label, $value)
    {
        foreach ($this->wrapText($label . ": " . $value, 92) as $line) {
            $this->ensureSpace(16);
            $this->text($line, $this->margin + 10, $this->y, 10, false, [55, 65, 81]);
            $this->y -= 14;
        }
    }

    private function addPage()
    {
        $this->pages[] = "";
        $this->currentPage = count($this->pages) - 1;
        $this->y = $this->height - $this->margin;

        $this->text("My Schedule", $this->margin, $this->y, 24, true, [17, 24, 39]);
        $this->y -= 22;
        $this->text($this->subtitle, $this->margin, $this->y, 10, false, [107, 114, 128]);
        $this->y -= 20;
        $this->line($this->margin, $this->y, $this->width - $this->margin, $this->y, [79, 70, 229]);
        $this->y -= 30;
    }

    private function ensureSpace($height)
    {
        if ($this->y - $height < $this->margin) {
            $this->addPage();
        }
    }

    private function text($text, $x, $y, $size, $bold, $color)
    {
        $font = $bold ? "F2" : "F1";
        $this->command($this->fillColor($color));
        $this->command(
            "BT /" . $font . " " . $this->num($size) . " Tf 1 0 0 1 " .
            $this->num($x) . " " . $this->num($y) . " Tm (" . $this->escapeText($text) . ") Tj ET"
        );
    }

    private function line($x1, $y1, $x2, $y2, $color)
    {
        $this->command($this->strokeColor($color));
        $this->command(
            $this->num($x1) . " " . $this->num($y1) . " m " .
            $this->num($x2) . " " . $this->num($y2) . " l S"
        );
    }

    private function command($command)
    {
        $this->pages[$this->currentPage] .= $command . "\n";
    }

    private function wrapText($text, $maxChars)
    {
        $text = $this->normalizeText($text);
        $wrapped = wordwrap($text, $maxChars, "\n", true);
        return explode("\n", $wrapped);
    }

    private function escapeText($text)
    {
        $text = $this->normalizeText($text);
        return str_replace(["\\", "(", ")"], ["\\\\", "\\(", "\\)"], $text);
    }

    private function normalizeText($text)
    {
        $text = html_entity_decode((string) $text, ENT_QUOTES | ENT_SUBSTITUTE, "UTF-8");
        $text = str_replace(["\r\n", "\r", "\n", "\t"], " ", $text);

        $collapsed = @preg_replace("/\s+/u", " ", trim($text));
        $text = $collapsed !== null ? $collapsed : preg_replace("/\s+/", " ", trim($text));

        if ($text === "") {
            return "-";
        }

        $text = $this->transliterateUtf8($text);

        if (function_exists("iconv")) {
            $converted = @iconv("UTF-8", "ASCII//TRANSLIT//IGNORE", $text);

            if ($converted !== false) {
                $text = $converted;
            }
        }

        $text = preg_replace("/[^\x20-\x7E]/", "", $text);
        $text = preg_replace("/\s+/", " ", trim($text));

        return $text === "" ? "-" : $text;
    }

    private function transliterateUtf8($text)
    {
        if (!preg_match("//u", $text)) {
            return $text;
        }

        $map = [
            0x00A0 => " ",
            0x2018 => "'",
            0x2019 => "'",
            0x201C => "\"",
            0x201D => "\"",
            0x2013 => "-",
            0x2014 => "-",
            0x2026 => "...",
            0x2116 => "No",
            0x0410 => "A",
            0x0430 => "a",
            0x0411 => "B",
            0x0431 => "b",
            0x0412 => "V",
            0x0432 => "v",
            0x0413 => "G",
            0x0433 => "g",
            0x0414 => "D",
            0x0434 => "d",
            0x0415 => "E",
            0x0435 => "e",
            0x0401 => "Yo",
            0x0451 => "yo",
            0x0416 => "Zh",
            0x0436 => "zh",
            0x0417 => "Z",
            0x0437 => "z",
            0x0418 => "I",
            0x0438 => "i",
            0x0419 => "Y",
            0x0439 => "y",
            0x041A => "K",
            0x043A => "k",
            0x041B => "L",
            0x043B => "l",
            0x041C => "M",
            0x043C => "m",
            0x041D => "N",
            0x043D => "n",
            0x041E => "O",
            0x043E => "o",
            0x041F => "P",
            0x043F => "p",
            0x0420 => "R",
            0x0440 => "r",
            0x0421 => "S",
            0x0441 => "s",
            0x0422 => "T",
            0x0442 => "t",
            0x0423 => "U",
            0x0443 => "u",
            0x0424 => "F",
            0x0444 => "f",
            0x0425 => "H",
            0x0445 => "h",
            0x0426 => "Ts",
            0x0446 => "ts",
            0x0427 => "Ch",
            0x0447 => "ch",
            0x0428 => "Sh",
            0x0448 => "sh",
            0x0429 => "Sht",
            0x0449 => "sht",
            0x042A => "A",
            0x044A => "a",
            0x042B => "Y",
            0x044B => "y",
            0x042C => "Y",
            0x044C => "y",
            0x042D => "E",
            0x044D => "e",
            0x042E => "Yu",
            0x044E => "yu",
            0x042F => "Ya",
            0x044F => "ya",
        ];

        $chars = preg_split("//u", $text, -1, PREG_SPLIT_NO_EMPTY);
        $result = "";

        foreach ($chars as $char) {
            $codepoint = $this->utf8Codepoint($char);
            $result .= isset($map[$codepoint]) ? $map[$codepoint] : $char;
        }

        return $result;
    }

    private function utf8Codepoint($char)
    {
        $bytes = array_values(unpack("C*", $char));
        $count = count($bytes);

        if ($count === 0) {
            return null;
        }

        if ($bytes[0] < 0x80) {
            return $bytes[0];
        }

        if (($bytes[0] & 0xE0) === 0xC0 && $count >= 2) {
            return (($bytes[0] & 0x1F) << 6) | ($bytes[1] & 0x3F);
        }

        if (($bytes[0] & 0xF0) === 0xE0 && $count >= 3) {
            return (($bytes[0] & 0x0F) << 12) | (($bytes[1] & 0x3F) << 6) | ($bytes[2] & 0x3F);
        }

        if (($bytes[0] & 0xF8) === 0xF0 && $count >= 4) {
            return (($bytes[0] & 0x07) << 18) | (($bytes[1] & 0x3F) << 12) | (($bytes[2] & 0x3F) << 6) | ($bytes[3] & 0x3F);
        }

        return null;
    }

    private function formatTime($time)
    {
        return substr((string) $time, 0, 5);
    }

    private function fillColor($rgb)
    {
        return $this->color($rgb) . " rg";
    }

    private function strokeColor($rgb)
    {
        return $this->color($rgb) . " RG";
    }

    private function color($rgb)
    {
        return $this->num($rgb[0] / 255, 3) . " " . $this->num($rgb[1] / 255, 3) . " " . $this->num($rgb[2] / 255, 3);
    }

    private function num($number, $precision = 2)
    {
        return number_format((float) $number, $precision, ".", "");
    }

    private function addObject(&$objects, $content)
    {
        $objects[] = $content;
        return count($objects) - 1;
    }

    private function buildPdf($objects, $catalogId)
    {
        $pdf = "%PDF-1.4\n";
        $offsets = [0];

        for ($i = 1; $i < count($objects); $i++) {
            $offsets[$i] = strlen($pdf);
            $pdf .= $i . " 0 obj\n" . $objects[$i] . "\nendobj\n";
        }

        $xrefOffset = strlen($pdf);
        $pdf .= "xref\n0 " . count($objects) . "\n";
        $pdf .= "0000000000 65535 f \n";

        for ($i = 1; $i < count($objects); $i++) {
            $pdf .= sprintf("%010d 00000 n \n", $offsets[$i]);
        }

        $pdf .= "trailer\n<< /Size " . count($objects) . " /Root " . $catalogId . " 0 R >>\n";
        $pdf .= "startxref\n" . $xrefOffset . "\n%%EOF";

        return $pdf;
    }
}
