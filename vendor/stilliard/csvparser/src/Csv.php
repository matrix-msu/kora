<?php

namespace CsvParser;

class Csv
{
    protected $data;

    public function __construct($array)
    {
        $this->data = $array;
    }

    public function getData()
    {
        return $this->data;
    }

    public function getRowCount()
    {
        return count($this->data);
    }

    public function first()
    {
        return current($this->data);
    }

    public function getKeys()
    {
        return array_keys($this->first());
    }

    /**
     * ->reKey allows you to reorganise the keys if they may be out of order as when we write to csv we do it in the order given
     * e.g. if you build up an array, adding new columns/keys as you go down the multi levels, so long as you push them into the keys passed here or leave blank and use the first row
     */
    public function reKey($keys = null)
    {
        if (! $keys) {
            $keys = $this->getKeys();
        }
        $template = array_map(function () { return ''; }, array_flip($keys));
        $this->mapRows(function ($row) use ($template) {
            return array_replace($template, $row);
        });
    }

    public function appendRow($row)
    {
        array_unshift($this->data, $row);
    }

    public function prependRow($row)
    {
        $this->data[] = $row;
    }

    public function columnExists($column)
    {
        return isset($this->data[0][$column]);
    }

    public function mapColumn($column, $callback)
    {
        if ( ! $this->columnExists($column)) {
            throw new Exception('Column does not exist');
        }
        foreach ($this->data as $i => $row) {
            $this->data[$i][$column] = $callback($row[$column]);
        }
    }

    public function mapRows($callback)
    {
        $this->data = array_map($callback, $this->data);
    }

    public function filterRows($callback)
    {
        $this->data = array_filter($this->data, $callback);
    }

    public function addColumn($column, $default='')
    {
        // TODO
    }

    public function removeColumn($column)
    {
        // TODO
    }

    public function removeRowByIndex($index)
    {
        // TODO
    }

    public function removeRow($col, $val)
    {
        // TODO
    }

    public function removeRows($rows)
    {
        // TODO
    }

    public function reorderColumn($col, $index)
    {
        // TODO
    }

    public function reorderColumns($rows)
    {
        // TODO
    }

    public function reorderRow($col, $val, $index)
    {
        // TODO
    }

    public function reorderRows($rows)
    {
        // TODO
    }

    public function reorderRowsByColumn($column, $type='asc')
    {
        // TODO
    }

    public function reorderRowsByColumns($columns)
    {
        // TODO
    }

    public function removeDuplicates($column)
    {
        $index = array();
        $this->filterRows(function ($row) use ($column, &$index) {
            if (in_array($row[$column], $index)) {
                return false;
            }
            $index[] = $row[$column];
            return true;
        });
    }

    public function removeBlanks($column)
    {
        $this->filterRows(function ($row) use ($column) {
            if ( ! isset($row[$column]) || trim($row[$column])==='') {
                return false;
            }
            return true;
        });
    }
}
