<?php

trait Permissions {

    /**
     * Get permissions represented in string.
     *
     * @return string
     */
    public function getPermissionsStringAttribute()
    {
        $permissions = $this->getAttribute("permissions");

        if (!empty($permissions))
        {
            $items = array();

            foreach ($permissions as $key => $value)
            {
                $items[] = "$key: $value";
            }

            return implode("\r\n", $items);
        }

        return null;
    }

    /**
     * Update permissions from a string.
     *
     * @param string $value
     */
    public function setPermissionsStringAttribute($value)
    {
        $permissions = array();

        $pattern = '/^([^:]+):\s*(-1|0|1)\s*$/m';
        if (false !== preg_match_all($pattern, $value, $matches, PREG_SET_ORDER))
        {
            foreach ($matches as $item)
            {
                $permissions[$item[1]] = $item[2];
            }
        }

        $this->setAttribute("permissions", $permissions);
    }
}