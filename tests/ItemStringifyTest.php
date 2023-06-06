<?php
namespace TurboLabIt\BaseCommand\tests;
use PHPUnit\Framework\TestCase;
use TurboLabIt\BaseCommand\Service\ItemStringify;

const TESTITEM_GETNAME  = "getName() called";
const TESTITEM_GETTITLE = "getTitle() called";

const PATH_PREFOLDER_NAME   = "MyItems";
const PATH_FOLDER_1         = "Folder1";
const PATH_FOLDER_2         = "Folder2";


class ItemStringifyTest extends TestCase
{
    protected ItemStringify $o;


    protected function setUp() : void
    {
        $this->o = new ItemStringify();
    }


    public function testNewInstance()
    {
        $this->assertInstanceOf(ItemStringify::class, $this->o);
    }


    public function testBuildItemNameObject()
    {
        $item = new TestItemName();
        $result = $this->o->buildItemName($item);
        $this->assertEquals(TESTITEM_GETNAME, $result);

        $item = new TestItemTitle();
        $result = $this->o->buildItemName($item);
        $this->assertEquals(TESTITEM_GETTITLE, $result);
    }


    public function testBuildItemNameArray()
    {
        $arrItem = ["dadas", "name" => TESTITEM_GETNAME, "title" => TESTITEM_GETTITLE];
        $result = $this->o->buildItemName($arrItem);
        $this->assertEquals(TESTITEM_GETNAME, $result);

        unset($arrItem["name"]);
        $result = $this->o->buildItemName($arrItem);
        $this->assertEquals(TESTITEM_GETTITLE, $result);
    }


    public function testBuildItemNameString()
    {
        $result = $this->o->buildItemName(TESTITEM_GETTITLE);
        $this->assertEquals(TESTITEM_GETTITLE, $result);
    }


    public function testBuildItemTitleNoKey()
    {
        $item = new TestItemName();
        $result = $this->o->buildItemTitle($item);
        $this->assertEquals(TESTITEM_GETNAME, $result);

        $item = new TestItemTitle();
        $result = $this->o->buildItemName($item);
        $this->assertEquals(TESTITEM_GETTITLE, $result);
    }


    public function testBuildItemTitleWithKey()
    {
        $key = time();
        $item = new TestItemName();
        $result = $this->o->buildItemTitle($item, $key);
        $this->assertEquals("[$key] " . TESTITEM_GETNAME, $result);

        $key = time();
        $item = new TestItemTitle();
        $result = $this->o->buildItemTitle($item, $key);
        $this->assertEquals("[$key] " . TESTITEM_GETTITLE, $result);
    }


    public function testBuildItemPath()
    {
        $item   = new TestItemName();
        $key = time();
        $result = $this->o->buildItemPath($item, $key, PATH_PREFOLDER_NAME);
        $expected = implode('/', [PATH_PREFOLDER_NAME, ("[$key] " . TESTITEM_GETNAME), '']);
        $this->assertEquals($expected, $result);

        $item   = new TestItemName();
        $key = time();
        $result = $this->o->buildItemPath($item, $key, [PATH_PREFOLDER_NAME, PATH_PREFOLDER_NAME]);
        $expected = implode('/', [PATH_PREFOLDER_NAME, PATH_PREFOLDER_NAME, ("[$key] " . TESTITEM_GETNAME), '']);
        $this->assertEquals($expected, $result);
    }


    public function testBuildItemPathWithSubfolder()
    {
        $item   = new TestItemName();
        $key = time();
        $result = $this->o->buildItemPath($item, $key, PATH_PREFOLDER_NAME, [PATH_FOLDER_1, PATH_FOLDER_2]);
        $expected = implode('/', [PATH_PREFOLDER_NAME, ("[$key] " . TESTITEM_GETNAME), PATH_FOLDER_1, PATH_FOLDER_2, '']);
        $this->assertEquals($expected, $result);
    }


    public function testSlugifyName()
    {
        $item = new TestItemName();
        $slug = $this->o->slugify($item);
        $this->assertEquals('getname-called', $slug);
    }


    public function testSlugifyTitle()
    {
        $item = new TestItemTitle();
        $slug = $this->o->slugify($item);
        $this->assertEquals('gettitle-called', $slug);
    }


    public function testSlugifyNameUpper()
    {
        $item = (new TestItemName())->setNameUpper();
        $slug = $this->o->slugify($item);
        $this->assertEquals('getname-called', $slug);
    }


    public function testSlugifyMultipleAsPath()
    {
        $item   = (new TestItemName())->setNameUpper();
        $item2  = (new TestItemTitle());
        $path   = $this->o->slugifyMultipleAsPath([$item, $item2]);
        $this->assertEquals('getname-called/gettitle-called', $path);
    }
}


class TestItemName
{
    protected string $name = TESTITEM_GETNAME;

    public function getName()
    {
        return $this->name;
    }

    public function setNameUpper() : self
    {
        $this->name = mb_strtoupper($this->name);
        return $this;
    }
}

class TestItemTitle
{
    public function getTitle()
    {
        return TESTITEM_GETTITLE;
    }
}
