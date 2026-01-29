<div class="col-{{ $col }} offset-{{ $set }}">
    <div class="form-group">
        <label for="{{ $id }}" class="text-muted" style="font-size: 13.5px; font-weight: bold; letter-spacing: 0.75px;">{{ $title }}</label>
        <select class="form-control" id="{{ $id }}" name="{{ $name }}" {{ $disabled ? ' disabled' : '' }}>
            {{ $slot }}
        </select>
    </div>
</div>
