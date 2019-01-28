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

use WP_UnitTestCase;
use Docalist\Batch\SearchReplace\Field;
use Docalist\Batch\Tests\SearchReplace\TestRecord;
use InvalidArgumentException;

/**
 * Teste la classe Field.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class FieldTest extends WP_UnitTestCase
{
    public function testAddField()
    {
        $field = new Field('test', Field::TYPE_OBJECT);
        $field->addField(new Field('a'));
        $this->assertTrue($field->hasFields());
    }

    public function testAddFieldInTextField()
    {
        $field = new Field('test');
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('not an object');
        $field->addField(new Field('a'));
    }

    public function testAddFieldAlreadyHasParent()
    {
        $parent = new Field('parent', Field::TYPE_OBJECT);
        $field = new Field('test');
        $parent->addField($field);

        $parent2 = new Field('parent2', Field::TYPE_OBJECT);
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('already has a parent');
        $parent2->addField($field);
    }

    public function testGetName()
    {
        $field = new Field('test');
        $this->assertSame('test', $field->getName());
    }

    public function testGetType()
    {
        $field = new Field('test');
        $this->assertSame(Field::TYPE_TEXT, $field->getType());

        foreach ([Field::TYPE_OBJECT, Field::TYPE_TEXT, Field::TYPE_VALUE] as $type) {
            $field = new Field('test', $type);
            $this->assertSame($type, $field->getType());
        }
    }

    public function testBadType()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid field type');

        new Field('test', 0);
    }

    public function testIsType()
    {
        $field = new Field('test', Field::TYPE_OBJECT);
        $this->assertTrue($field->isObject());
        $this->assertFalse($field->isText());
        $this->assertFalse($field->isValue());

        $field = new Field('test', Field::TYPE_TEXT);
        $this->assertFalse($field->isObject());
        $this->assertTrue($field->isText());
        $this->assertFalse($field->isValue());

        $field = new Field('test', Field::TYPE_VALUE);
        $this->assertFalse($field->isObject());
        $this->assertFalse($field->isText());
        $this->assertTrue($field->isValue());
    }

    public function testIsRepeatable()
    {
        $field = new Field('test', Field::TYPE_TEXT);
        $this->assertFalse($field->isRepeatable());

        $field = new Field('test', Field::TYPE_TEXT, false);
        $this->assertFalse($field->isRepeatable());

        $field = new Field('test', Field::TYPE_TEXT, true);
        $this->assertTrue($field->isRepeatable());
    }

    public function testHasParent()
    {
        $field = new Field('field');
        $this->assertFalse($field->hasParent());

        $parent = new Field('parent', Field::TYPE_OBJECT);
        $parent->addField($field);

        $this->assertTrue($field->hasParent());
    }

    public function testGetParent()
    {
        $field = new Field('field', Field::TYPE_TEXT);
        $this->assertNull($field->getParent());

        $parent = new Field('parent', Field::TYPE_OBJECT);
        $parent->addField($field);

        $this->assertSame($parent, $field->getParent());
    }

    public function testGetKey()
    {
        $parent = new Field('parent', Field::TYPE_OBJECT);
        $this->assertSame('parent', $parent->getKey());

        $field = new Field('field', Field::TYPE_OBJECT);
        $this->assertSame('field', $field->getKey());

        $parent->addField($field);
        $this->assertSame('parent.field', $field->getKey());

        $subfield = new Field('subfield', Field::TYPE_TEXT, false);
        $field->addField($subfield);
        $this->assertSame('parent.field.subfield', $subfield->getKey());
    }

    public function testGetLabel()
    {
        // on teste juste que ça retourne des libellés différents selon le type et repeat et que ça contient la clé
        $seen = [];
        foreach ([Field::TYPE_OBJECT, Field::TYPE_TEXT, Field::TYPE_VALUE] as $type) {
            foreach ([false, true] as $repeat) {
                $field = new Field('test', $type, $repeat);
                $label = $field->getLabel();

                $this->assertTrue(false !== strpos($label, $field->getKey()));
                $this->assertFalse(isset($seen[$label]));
                $seen[$label] = true;
            }
        }
    }
}
