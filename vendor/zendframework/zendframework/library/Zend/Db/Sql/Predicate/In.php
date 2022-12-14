<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\Sql\Predicate;

use Zend\Db\Sql\Exception;
use Zend\Db\Sql\Select;

class In implements PredicateInterface
{
    protected $identifier;
    protected $valueSet;

    protected $specification = '%s IN %s';

    /**
     * Constructor
     *
     * @param null|string|array $identifier
     * @param null|array|Select $valueSet
     */
    public function __construct($identifier = null, $valueSet = null)
    {
        if ($identifier) {
            $this->setIdentifier($identifier);
        }
        if ($valueSet) {
            $this->setValueSet($valueSet);
        }
    }

    /**
     * Set identifier for comparison
     *
     * @param  string|array $identifier
     * @return In
     */
    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;

        return $this;
    }

    /**
     * Get identifier of comparison
     *
     * @return null|string|array
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * Set set of values for IN comparison
     *
     * @param  array|Select                       $valueSet
     * @throws Exception\InvalidArgumentException
     * @return In
     */
    public function setValueSet($valueSet)
    {
        if (!is_array($valueSet) && !$valueSet instanceof Select) {
            throw new Exception\InvalidArgumentException(
                '$valueSet must be either an array or a Zend\Db\Sql\Select object, ' . gettype($valueSet) . ' given'
            );
        }
        $this->valueSet = $valueSet;

        return $this;
    }

    /**
     * Gets set of values in IN comparision
     *
     * @return array|Select
     */
    public function getValueSet()
    {
        return $this->valueSet;
    }

    /**
     * Return array of parts for where statement
     *
     * @return array
     */
    public function getExpressionData()
    {
        $identifier = $this->getIdentifier();
        $values = $this->getValueSet();
        $replacements = array();

        if (is_array($identifier)) {
            // count????????????
            $identifierCount = 0;
            if (!empty($identifier)){
                $identifierCount = count($identifier);
            }

            $identifierSpecFragment = '(' . implode(', ', array_fill(0, $identifierCount, '%s')) . ')';
            $types = array_fill(0, $identifierCount, self::TYPE_IDENTIFIER);
            $replacements = $identifier;
        } else {
            $identifierSpecFragment = '%s';
            $replacements[] = $identifier;
            $types = array(self::TYPE_IDENTIFIER);
        }

        if ($values instanceof Select) {
            $specification = vsprintf(
                $this->specification,
                array($identifierSpecFragment, '%s')
            );
            $replacements[] = $values;
            $types[] = self::TYPE_VALUE;
        } else {
             // count????????????
             $valuesCount = 0;
             if (!empty($values)){
                 $valuesCount = count($values);
             }

            $specification = vsprintf(
                $this->specification,
                array($identifierSpecFragment, '(' . implode(', ', array_fill(0, $valuesCount, '%s')) . ')')
            );
            $replacements = array_merge($replacements, $values);
            $types = array_merge($types, array_fill(0, $valuesCount, self::TYPE_VALUE));
        }

        return array(array(
            $specification,
            $replacements,
            $types,
        ));
    }
}
