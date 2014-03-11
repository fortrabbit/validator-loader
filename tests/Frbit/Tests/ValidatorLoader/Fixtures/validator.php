<?php
/**
 * This class is part of ValidatorLoader
 */

return array(
    'validators' => array(
        'name' => array(
            'rules'    => array(
                'parameter' => array(
                    'min:3',
                    'max:6',
                )
            ),
            'messages' => array(
                'parameter.min' => 'Too short',
                'parameter.max' => 'Too long'
            )
        )
    )
);