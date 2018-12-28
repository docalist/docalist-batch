<?php declare(strict_types=1);
/**
 * This file is part of Docalist Batch.
 *
 * Copyright (C) 2012-2018 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */
namespace Docalist\Batch\Tests\SearchReplace;

use PHPUnit_Framework_TestCase;
use Docalist\Batch\SearchReplace\Field;
use Docalist\Batch\SearchReplace\Fields;
use InvalidArgumentException;

/**
 * Teste la classe Field.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class FieldsTest extends PHPUnit_Framework_TestCase
{
    public function testAddField()
    {
        $fields = new Fields();
        $fields->addField(new Field('test'));
        $this->assertTrue($fields->hasFields());
    }

    public function testAddFieldDuplicateKey()
    {
        $fields = new Fields();
        $fields->addField(new Field('test'));
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Duplicate field "test"');
        $fields->addField(new Field('test'));
    }

    public function testHasField()
    {
        $fields = new Fields();
        $this->assertFalse($fields->hasField('test'));
        $fields->addField(new Field('test'));
        $this->assertTrue($fields->hasField('test'));
    }

    public function testGetField()
    {
        $fields = new Fields();
        $field = new Field('test');
        $fields->addField($field);
        $this->assertSame($field, $fields->getField('test'));
    }

    public function testGetFieldNotFound()
    {
        $fields = new Fields();
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Field not found "test"');
        $fields->getField('test');
    }

    public function testGetFields()
    {
        $fields = new Fields();
        $this->assertSame([], $fields->getFields());

        $field1 = new Field('field1');
        $fields->addField($field1);
        $this->assertSame(['field1' => $field1], $fields->getFields());

        $field2 = new Field('field2');
        $fields->addField($field2);
        $this->assertSame(['field1' => $field1, 'field2' => $field2], $fields->getFields());
    }

    public function testGetFieldsAsSelectOptions()
    {
        $fields = new Fields();
        $this->assertSame([], $fields->getFieldsAsSelectOptions());

        $field1 = new Field('zzz');
        $fields->addField($field1);

        $field2 = new Field('aaa');
        $fields->addField($field2);

        $this->assertSame([
            'aaa' => $field2->getLabel(),
            'zzz' => $field1->getLabel(),

        ], $fields->getFieldsAsSelectOptions());

        $group = new Field('group', Field::TYPE_OBJECT);
        $fields->addField($group);

        $subz = new Field('subz');
        $group->addField($subz);
        $suba = new Field('suba');
        $group->addField($suba);

        $this->assertSame([
            'aaa' => $field2->getLabel(),
            $group->getLabel() => [
                'group.suba' => $suba->getLabel(),
                'group.subz' => $subz->getLabel(),
            ],
            'zzz' => $field1->getLabel(),
        ], $fields->getFieldsAsSelectOptions());
    }
}
