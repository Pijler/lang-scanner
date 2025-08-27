<div class="mt-2 mx-2">
  <div class="flex space-x-1">
    <span class="flex-1 content-repeat-[─] text-gray">

    </span>
  </div>

  <div>
    <span>
      <div class="flex space-x-1">
        @php
          $count = $issues->sum(fn ($issue) => $issue->count());
        @endphp

        @if ($count == 0)
          <span class="px-2 bg-green text-gray uppercase font-bold">
            NO ISSUES
          </span>
        @elseif(!$testing)
          <span class="px-2 bg-gray text-gray uppercase font-bold">
            SCANNED
          </span>
        @else
          <span class="px-2 bg-red text-white uppercase font-bold">
            FAIL
          </span>
        @endif

        <span class="flex-1 content-repeat-[.] text-gray"></span>
        <span>
          <span>
            {{ $totalFiles }} {{ str('file')->plural($totalFiles) }}
          </span>

          @if ($issues->isNotEmpty())
            <span>
              @if ($testing)
                , Missing {{ $count }} {{ str('translation')->plural($count) }}
              @else
                , New {{ $count }} {{ str('translation')->plural($count) }} scanned
              @endif
            </span>
          @endif
        </span>
      </div>
    </span>
  </div>
</div>
