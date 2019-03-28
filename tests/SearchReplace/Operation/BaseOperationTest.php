<?php declare(strict_types=1);
/**
 * This file is part of Docalist Batch.
 *
 * Copyright (C) 2012-2019 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */
namespace Docalist\Batch\Tests\SearchReplace\Operation;

use PHPUnit_Framework_TestCase;
use Docalist\Batch\SearchReplace\Operation\BaseOperation;
use Docalist\Batch\SearchReplace\Field;
use Docalist\Batch\Tests\SearchReplace\TestRecord;
use InvalidArgumentException;

/**
 * Teste la classe BaseOperation.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class BaseOperationTest extends PHPUnit_Framework_TestCase
{
    private function newBaseOperation(Field $field, string $search, string $replace)
    {
        return $this->getMockForAbstractClass(BaseOperation::class, [$field, $search, $replace]);
    }

    public function testConstructWithObjectField()
    {
        $field = new Field('test', Field::TYPE_OBJECT);
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid field type');
        $this->newBaseOperation($field, 'search', 'replace');
    }

    public function testGetField()
    {
        $field = new Field('field');
        $operation = $this->newBaseOperation($field, 'search', 'replace');
        $this->assertSame($field, $operation->getField());
    }

    public function testGetSearch()
    {
        $operation = $this->newBaseOperation(new Field('field'), 'search', 'replace');
        $this->assertSame('search', $operation->getSearch());
    }

    public function testGetReplace()
    {
        $operation = $this->newBaseOperation(new Field('field'), 'search', 'replace');
        $this->assertSame('replace', $operation->getReplace());
    }

    public function testGetExplanation()
    {
        $operation = $this->newBaseOperation(new Field('field', Field::TYPE_TEXT, false), 'search', 'replace');
        $expected = 'Remplacer <del>search</del> par <ins>replace</ins> dans le champ <var>field (texte)</var>.';
        $this->assertSame($expected, $operation->getExplanation());
    }

    public function testProcess()
    {
        $operation = $this->newBaseOperation(new Field('field'), 'search', 'replace');
        $this->assertFalse($operation->process(new TestRecord()));
    }
}
