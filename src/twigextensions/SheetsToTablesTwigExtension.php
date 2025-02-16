<?php
/**
 * SheetsToTablesTwigExtension
 * 
 * @link      https://miranj.in/
 * @copyright Copyright (c) 2018 Miranj
 */

namespace miranj\sheetstotables\twigextensions;

use Craft;
use craft\elements\Asset;
use craft\i18n\Locale;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer;
use PhpOffice\PhpSpreadsheet\Shared\StringHelper;
use Twig\Extension\AbstractExtension;
use Twig\Markup;
use Twig\TwigFunction;

class SheetsToTablesTwigExtension extends AbstractExtension
{
    public function getName()
    {
        return 'SheetsToTables';
    }
    
    public function getFunctions()
    {
        return [
          new TwigFunction('sheetstotables', [$this, 'sheetstotables']),
        ];
    }
    
    public function sheetstotables(Asset $file = null)
    {
        // Create a temp copy of asset because PHPSpreadSheet
        // works with files, not streams
        $path = $file->getCopyOfFile();
        
        // Code based on PHPSpreadSheet Docs
        // - https://phpspreadsheet.readthedocs.io/en/develop/topics/reading-files/
        // - https://phpspreadsheet.readthedocs.io/en/develop/topics/reading-and-writing-to-file/#html
        
        // Figure out file type
        $inputFileType = IOFactory::identify($path);
        $reader = IOFactory::createReader($inputFileType);
        $reader->setReadDataOnly(false);
        
        // Read
        $spreadsheet = $reader->load($path);
        
        // Setup locale
        $currentLocale = Craft::$app->getLocale();
        StringHelper::setDecimalSeparator(
            $currentLocale->getNumberSymbol(Locale::SYMBOL_DECIMAL_SEPARATOR)
        );
        StringHelper::setThousandsSeparator(
            $currentLocale->getNumberSymbol(Locale::SYMBOL_GROUPING_SEPARATOR)
        );
        
        // Export HTML
        $writer = new Writer\Html($spreadsheet);
        $styles = $writer->generateStyles(true);
        $styles = trim($styles);
        $writer->setUseInlineCss(false);
        
        $result = $writer->generateSheetData();
        
        $result = trim($result);
        //$result = preg_replace('/^<style>.*?<\\/style>/is', '', $result);
        
        return new Markup($styles . $result, 'UTF-8');
    }
}
