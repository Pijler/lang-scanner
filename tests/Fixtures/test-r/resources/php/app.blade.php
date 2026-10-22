<div>
  {{ __('used') }}
  <br />
  {{ __('group.used') }}
  <br />
  @foreach (__('menu') as $item)
    {{ $item }}
  @endforeach
</div>
