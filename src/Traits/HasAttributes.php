<?php


namespace WhoJonson\LaravelAuth\Traits;

use Illuminate\Support\Str;

trait HasAttributes
{
    /**
     * Get an attribute from the model.
     *
     * @param string $key
     * @return mixed|void
     */
    public function getAttribute(string $key) {
        if(!$key || !in_array($key, $this->keys)) {
            return;
        }

        if (array_key_exists($key, $this->attributes) || $this->hasGetMutator($key)) {
            return $this->getAttributeValue($key);
        }

        if (method_exists(self::class, $key)) {
            return;
        }

        return array_key_exists($key, $this->attributes)
            ? $this->attributes[$key]
            : null;
    }

    /**
     * Get an attribute from the $attributes array.
     *
     * @param string $key
     * @return mixed
     */
    protected function getAttributeFromArray(string $key)
    {
        return $this->attributes[$key] ?? null;
    }

    /**
     * Determine if a get mutator exists for an attribute.
     *
     * @param string $key
     * @return bool
     */
    public function hasGetMutator(string $key): bool
    {
        return method_exists($this, 'get'.Str::studly($key).'Attribute');
    }

    /**
     * Get the value of an attribute using its mutator.
     *
     * @param string $key
     * @param mixed $value
     * @return mixed
     */
    protected function mutateAttribute(string $key, $value)
    {
        return $this->{'get' . Str::studly($key) . 'Attribute'}($value);
    }

    /**
     * Get a plain attribute (not a relationship).
     *
     * @param string $key
     * @return mixed
     */
    public function getAttributeValue(string $key) {
        $value = $this->getAttributeFromArray($key);

        // If the attribute has a get mutator, we will call that then return what
        // it returns as the value, which is useful for transforming values on
        // retrieval from the model to a form that is more useful for usage.
        if ($this->hasGetMutator($key)) {
            return $this->mutateAttribute($key, $value);
        }

        return $value;
    }

    /**
     * Set the array of model attributes
     *
     * @param  array  $attributes
     * @return mixed
     */
    public function setAttributes(array $attributes)
    {
        foreach ($attributes as $key => $value) {
            $this->setAttribute($key, $value);
        }

        return $this;
    }

    /**
     * Determine if a set mutator exists for an attribute.
     *
     * @param string $key
     * @return bool
     */
    public function hasSetMutator(string $key): bool
    {
        return method_exists($this, 'set'.Str::studly($key).'Attribute');
    }

    /**
     * Set the value of an attribute using its mutator.
     *
     * @param string $key
     * @param mixed $value
     * @return mixed
     */
    protected function setMutatedAttributeValue(string $key, $value)
    {
        return $this->{'set' . Str::studly($key) . 'Attribute'}($value);
    }

    /**
     * Set a given attribute on the model.
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function setAttribute(string $key, $value) {
        if ($this->hasSetMutator($key)) {
            return $this->setMutatedAttributeValue($key, $value);
        }
        if($key == $this->primaryKey) {
            $this->setPrimaryKey($value);
        } else {
            $this->attributes[$key] = $value;
        }
    }

    /**
     * Set the array of model attributes(original from store)
     *
     * @param  array  $attributes
     * @return mixed|$this
     */
    public function setOriginals(array $attributes)
    {
        foreach ($attributes as $key => $value) {
            if(in_array($key, $this->keys)) {
                $this->originals[$key] = $value;
            }
        }
        return $this;
    }

    /**
     * @param string $key
     *
     * @return string|null
     */
    public function getOriginal(string $key) {
        return $this->originals[$key];
    }

    /**
     * @param string|int|null
     */
    public function setPrimaryKey($id = null) {
        /*if ($this->primaryKeyType == 'int' && $this->incrementing) {
            $id = ((int) $this->newQuery()->max($this->primaryKey)) + 1;
        }*/
        $this->attributes[$this->primaryKey] = $id;
    }
}
