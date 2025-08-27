<div class="flex space-x-1 mx-2">
  <span>
    @if ($issue->color() == 'red')
      <span class="text-red font-bold">
        {{ $issue->symbol() }}
      </span>
    @elseif($issue->color() == 'gray')
      <span class="text-gray font-bold">
        {{ $issue->symbol() }}
      </span>
    @elseif($issue->color() == 'green')
      <span class="text-green font-bold">
        {{ $issue->symbol() }}
      </span>
    @endif

    <span class="ml-1">
      {{ $issue->file() }}
    </span>
  </span>

  <span class="flex-1 text-gray text-right {{ $isVerbose ? '' : 'truncate' }}">
    {{ $issue->description() }}
  </span>
</div>
