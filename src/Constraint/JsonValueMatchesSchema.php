<?php
namespace Helmich\JsonAssert\Constraint;

use JsonSchema\Validator;
use PHPUnit_Framework_Constraint as Constraint;
use stdClass;

/**
 * A constraint for asserting that a JSON document matches a schema
 *
 * @package    Helmich\JsonAssert
 * @subpackage Constraint
 */
class JsonValueMatchesSchema extends Constraint
{
    /**
     * @var array|stdClass
     */
    private $schema;

    /**
     * JsonValueMatchesSchema constructor.
     *
     * @param array|stdClass $schema The JSON schema
     */
    public function __construct($schema)
    {
        parent::__construct();
        $this->schema = $this->forceToObject($schema);
    }

    /**
     * VERY dirty hack to force a JSON document into an instance of the stdClass class.
     *
     * Yell if you can think of something better.
     *
     * @param array|stdClass $jsonDocument
     * @return stdClass
     */
    private function forceToObject($jsonDocument)
    {
        if (is_string($jsonDocument)) {
            return json_decode($jsonDocument);
        }

        return json_decode(json_encode($jsonDocument));
    }

    /**
     * @inheritdoc
     */
    protected function matches($other)
    {
        $other = $this->forceToObject($other);

        $validator = new Validator();
        $validator->check($other, $this->schema);

        return $validator->isValid();
    }

    /**
     * @inheritdoc
     */
    protected function additionalFailureDescription($other)
    {
        $other = $this->forceToObject($other);

        $validator = new Validator();
        $validator->check($other, $this->schema);

        return implode("\n", array_map(function ($error) {
            return $error['message'];
        }, $validator->getErrors()));
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'matches JSON schema';
    }
}
