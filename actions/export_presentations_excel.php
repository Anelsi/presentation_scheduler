<?php

session_start();

require_once "../config/db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../pages/login.php");
    exit;
}

$columns = [
    "title",
    "description",
    "presenter",
    "category",
    "presentation_date",
    "start_time",
    "end_time",
    "room"
];

$stmt = $pdo->query("
    SELECT
        title,
        description,
        presenter,
        category,
        presentation_date,
        start_time,
        end_time,
        room
    FROM presentations
    ORDER BY presentation_date ASC, start_time ASC, title ASC
");

$presentations = $stmt->fetchAll(PDO::FETCH_ASSOC);

function xmlEscape($value)
{
    return htmlspecialchars((string) $value, ENT_XML1 | ENT_COMPAT, "UTF-8");
}

function xlsxCell($cellReference, $value, $styleId = null)
{
    $styleAttribute = $styleId === null ? "" : ' s="' . $styleId . '"';

    return '<c r="' . $cellReference . '"' . $styleAttribute . ' t="inlineStr"><is><t xml:space="preserve">' .
        xmlEscape($value) .
        '</t></is></c>';
}

function dosDateTime($timestamp)
{
    $year = (int) date("Y", $timestamp);

    if ($year < 1980) {
        $year = 1980;
    }

    $dosDate = (($year - 1980) << 9)
        | ((int) date("n", $timestamp) << 5)
        | (int) date("j", $timestamp);

    $dosTime = ((int) date("G", $timestamp) << 11)
        | ((int) date("i", $timestamp) << 5)
        | ((int) floor((int) date("s", $timestamp) / 2));

    return [$dosDate, $dosTime];
}

function buildXlsxZip($files)
{
    $zip = "";
    $centralDirectory = "";
    $offset = 0;
    $timestamp = time();
    [$dosDate, $dosTime] = dosDateTime($timestamp);

    foreach ($files as $filename => $data) {
        $crc = crc32($data);

        if ($crc < 0) {
            $crc += 4294967296;
        }

        $size = strlen($data);
        $filenameLength = strlen($filename);

        $localHeader = pack(
            "VvvvvvVVVvv",
            0x04034b50,
            20,
            0,
            0,
            $dosTime,
            $dosDate,
            $crc,
            $size,
            $size,
            $filenameLength,
            0
        );

        $zip .= $localHeader . $filename . $data;

        $centralDirectory .= pack(
            "VvvvvvvVVVvvvvvVV",
            0x02014b50,
            20,
            20,
            0,
            0,
            $dosTime,
            $dosDate,
            $crc,
            $size,
            $size,
            $filenameLength,
            0,
            0,
            0,
            0,
            0,
            $offset
        ) . $filename;

        $offset += strlen($localHeader) + $filenameLength + $size;
    }

    $centralDirectorySize = strlen($centralDirectory);
    $centralDirectoryOffset = strlen($zip);
    $fileCount = count($files);

    $endOfCentralDirectory = pack(
        "VvvvvVVv",
        0x06054b50,
        0,
        0,
        $fileCount,
        $fileCount,
        $centralDirectorySize,
        $centralDirectoryOffset,
        0
    );

    return $zip . $centralDirectory . $endOfCentralDirectory;
}

$columnLetters = ["A", "B", "C", "D", "E", "F", "G", "H"];
$lastRowNumber = max(1, count($presentations) + 1);
$tableReference = "A1:H" . $lastRowNumber;

$sheetRows = '<row r="1">';

foreach ($columns as $index => $column) {
    $sheetRows .= xlsxCell($columnLetters[$index] . "1", $column, 1);
}

$sheetRows .= '</row>';

foreach ($presentations as $rowIndex => $presentation) {
    $excelRowNumber = $rowIndex + 2;
    $sheetRows .= '<row r="' . $excelRowNumber . '">';

    foreach ($columns as $columnIndex => $column) {
        $sheetRows .= xlsxCell(
            $columnLetters[$columnIndex] . $excelRowNumber,
            $presentation[$column]
        );
    }

    $sheetRows .= '</row>';
}

$tableColumns = "";

foreach ($columns as $index => $column) {
    $tableColumns .= '<tableColumn id="' . ($index + 1) . '" name="' . xmlEscape($column) . '"/>';
}

$createdAt = gmdate("Y-m-d\TH:i:s\Z");

$files = [
    "[Content_Types].xml" => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
    <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
    <Default Extension="xml" ContentType="application/xml"/>
    <Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>
    <Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>
    <Override PartName="/xl/tables/table1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.table+xml"/>
    <Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>
    <Override PartName="/docProps/core.xml" ContentType="application/vnd.openxmlformats-package.core-properties+xml"/>
    <Override PartName="/docProps/app.xml" ContentType="application/vnd.openxmlformats-officedocument.extended-properties+xml"/>
</Types>',
    "_rels/.rels" => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
    <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>
    <Relationship Id="rId2" Type="http://schemas.openxmlformats.org/package/2006/relationships/metadata/core-properties" Target="docProps/core.xml"/>
    <Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/extended-properties" Target="docProps/app.xml"/>
</Relationships>',
    "docProps/core.xml" => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<cp:coreProperties xmlns:cp="http://schemas.openxmlformats.org/package/2006/metadata/core-properties" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:dcterms="http://purl.org/dc/terms/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
    <dc:title>Presentations Export</dc:title>
    <dc:creator>Presentation Scheduler</dc:creator>
    <cp:lastModifiedBy>Presentation Scheduler</cp:lastModifiedBy>
    <dcterms:created xsi:type="dcterms:W3CDTF">' . $createdAt . '</dcterms:created>
    <dcterms:modified xsi:type="dcterms:W3CDTF">' . $createdAt . '</dcterms:modified>
</cp:coreProperties>',
    "docProps/app.xml" => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Properties xmlns="http://schemas.openxmlformats.org/officeDocument/2006/extended-properties" xmlns:vt="http://schemas.openxmlformats.org/officeDocument/2006/docPropsVTypes">
    <Application>Presentation Scheduler</Application>
</Properties>',
    "xl/workbook.xml" => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
    <sheets>
        <sheet name="Presentations" sheetId="1" r:id="rId1"/>
    </sheets>
</workbook>',
    "xl/_rels/workbook.xml.rels" => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
    <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>
    <Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>
</Relationships>',
    "xl/styles.xml" => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
    <fonts count="2">
        <font><sz val="11"/><color theme="1"/><name val="Calibri"/><family val="2"/></font>
        <font><b/><sz val="11"/><color rgb="FFFFFFFF"/><name val="Calibri"/><family val="2"/></font>
    </fonts>
    <fills count="3">
        <fill><patternFill patternType="none"/></fill>
        <fill><patternFill patternType="gray125"/></fill>
        <fill><patternFill patternType="solid"><fgColor rgb="FF4F46E5"/><bgColor indexed="64"/></patternFill></fill>
    </fills>
    <borders count="1">
        <border><left/><right/><top/><bottom/><diagonal/></border>
    </borders>
    <cellStyleXfs count="1">
        <xf numFmtId="0" fontId="0" fillId="0" borderId="0"/>
    </cellStyleXfs>
    <cellXfs count="2">
        <xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>
        <xf numFmtId="0" fontId="1" fillId="2" borderId="0" xfId="0" applyFont="1" applyFill="1"/>
    </cellXfs>
    <cellStyles count="1">
        <cellStyle name="Normal" xfId="0" builtinId="0"/>
    </cellStyles>
    <dxfs count="0"/>
    <tableStyles count="1" defaultTableStyle="TableStyleMedium2" defaultPivotStyle="PivotStyleLight16"/>
</styleSheet>',
    "xl/worksheets/sheet1.xml" => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
    <dimension ref="' . $tableReference . '"/>
    <sheetViews>
        <sheetView workbookViewId="0">
            <pane ySplit="1" topLeftCell="A2" activePane="bottomLeft" state="frozen"/>
        </sheetView>
    </sheetViews>
    <sheetFormatPr defaultRowHeight="15"/>
    <cols>
        <col min="1" max="1" width="28" customWidth="1"/>
        <col min="2" max="2" width="42" customWidth="1"/>
        <col min="3" max="3" width="22" customWidth="1"/>
        <col min="4" max="4" width="16" customWidth="1"/>
        <col min="5" max="5" width="18" customWidth="1"/>
        <col min="6" max="7" width="14" customWidth="1"/>
        <col min="8" max="8" width="16" customWidth="1"/>
    </cols>
    <sheetData>' . $sheetRows . '</sheetData>
    <autoFilter ref="' . $tableReference . '"/>
    <tableParts count="1">
        <tablePart r:id="rId1"/>
    </tableParts>
</worksheet>',
    "xl/worksheets/_rels/sheet1.xml.rels" => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
    <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/table" Target="../tables/table1.xml"/>
</Relationships>',
    "xl/tables/table1.xml" => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<table xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" id="1" name="PresentationsTable" displayName="PresentationsTable" ref="' . $tableReference . '" totalsRowShown="0">
    <autoFilter ref="' . $tableReference . '"/>
    <tableColumns count="' . count($columns) . '">' . $tableColumns . '</tableColumns>
    <tableStyleInfo name="TableStyleMedium2" showFirstColumn="0" showLastColumn="0" showRowStripes="1" showColumnStripes="0"/>
</table>'
];

$xlsx = buildXlsxZip($files);
$filename = "presentations_export_" . date("Y-m-d_H-i-s") . ".xlsx";

header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
header("Content-Disposition: attachment; filename=\"" . $filename . "\"");
header("Content-Length: " . strlen($xlsx));
header("Pragma: no-cache");
header("Expires: 0");

echo $xlsx;
exit;
