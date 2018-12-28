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
use Docalist\Batch\SearchReplace\FieldsBuilder;
use Docalist\Batch\Tests\SearchReplace\TestRecord;
use Docalist\Batch\SearchReplace\Field;
use Docalist\Batch\SearchReplace\Fields;

// use Docalist\Batch\SearchReplace\Field;
// use Docalist\Batch\SearchReplace\Fields;
// use InvalidArgumentException;
// use Docalist\Search\Aggregation\Bucket\TermsAggregation;

/**
 * Teste la classe Field.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class FieldsBuilderTest extends PHPUnit_Framework_TestCase
{
    public function testUn()
    {
        $record = new TestRecord();
        $schema = $record->getSchema();

        $builder = new FieldsBuilder();
        $builder->addFieldsFromSchema($schema);
// echo '<pre>';
// var_export($builder->getField('object'));
// echo '</pre>';
        $this->check($builder, 'text', Field::TYPE_TEXT);
        $this->check($builder, 'texts', Field::TYPE_TEXT, true);
        $this->check($builder, 'value', Field::TYPE_VALUE);
        $this->check($builder, 'values', Field::TYPE_VALUE, true);
        $this->check($builder, 'object', Field::TYPE_OBJECT);
        $this->check($builder, 'objects', Field::TYPE_OBJECT, true);

        $object = $builder->getField('object');
        $this->check($object, 'text', Field::TYPE_TEXT);
        $this->check($object, 'texts', Field::TYPE_TEXT, true);
        $this->check($object, 'value', Field::TYPE_VALUE);
        $this->check($object, 'values', Field::TYPE_VALUE, true);

        $objects = $builder->getField('objects');
        $this->check($objects, 'text', Field::TYPE_TEXT);
        $this->check($objects, 'texts', Field::TYPE_TEXT, true);
        $this->check($objects, 'value', Field::TYPE_VALUE);
        $this->check($objects, 'values', Field::TYPE_VALUE, true);
    }

    private function check(Fields $fields, string $name, int $type, bool $repeat = false)
    {
        $this->assertTrue($fields->hasField($name));
        $field = $fields->getField($name);
        $this->assertSame($type, $field->getType());
        $this->assertSame($repeat, $field->isRepeatable());


        if ($fields instanceof FieldsBuilder) {
            return;

        }
        $this->assertTrue($field->hasParent());
        $this->assertSame($fields, $field->getParent());
        $this->assertTrue($fields->hasField($name));
    }
}
