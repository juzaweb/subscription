{{ Field::select(
    trans('subscription::content.method'),
     'method',
     [
         'id' => 'select-payment-method',
         'options' => $methodOptions
     ]
     )
}}
