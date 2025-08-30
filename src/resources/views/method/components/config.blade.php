@foreach($fields as $name => $label)
    {{ Field::text($label, "config[{$name}]", ['value' => $config[$name] ?? null]) }}
@endforeach

@if($hasSandbox)
    @foreach($fields as $name => $label)
        {{ Field::text($label . ' (Sandbox)', "config[sandbox_{$name}]", ['value' => $config["sandbox_{$name}"] ?? null]) }}
    @endforeach
@endif
