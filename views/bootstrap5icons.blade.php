@if (count($breadcrumbs))

    <ol class="breadcrumb">
        @foreach ($breadcrumbs as $breadcrumb)

            @if ($breadcrumb->url && !$loop->last)
                <li class="breadcrumb-item">
                    <a href="{{ $breadcrumb->url }}">
                        <i class="{{ $breadcrumb->faIcon ?? '' }} me-2" aria-hidden="true"></i>{!! $breadcrumb->title !!}
                    </a>
                </li>
            @else
                <li class="breadcrumb-item active" aria-current="page">
                    <i class="{{ $breadcrumb->faIcon ?? '' }} me-2" aria-hidden="true"></i>{!! $breadcrumb->title !!}
                </li>
            @endif

        @endforeach
    </ol>

@endif
