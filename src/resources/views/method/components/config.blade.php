@foreach($fields as $name => $label)
    {{ Field::text($label, "config[{$name}]", ['value' => $config[$name] ?? null]) }}
@endforeach

@if($hasSandbox)
    {{ Field::checkbox(__('Test Mode'), 'config[sandbox]', ['value' => $config['sandbox'] ?? false]) }}
@endif
