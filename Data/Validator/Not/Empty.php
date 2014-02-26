<?php

namespace ice\core\data\validator;

use ice\core\Data_Validator;

class Not_Empty extends Data_Validator
{

    public function validate($value)
    {
        return (bool)$value;
    }

}