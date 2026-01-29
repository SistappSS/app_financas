<div class="col-{{ $col }} offset-{{ $set }} mb-2">
    <div class="form-group">
        <label class="form-control-label text-muted" style="font-size: 13.5px; font-weight: bold; letter-spacing: 0.75px;" for="{{ $id }}">{{ $title }}</label>
        <div class="input-group">
            <input type="{{ $type }}" class="form-control form-control-sm" name="{{ $name }}" id="{{ $id }}"
                   value="{{ $value }}" placeholder="{{ $placeholder }}" {{ $disabled ? ' disabled' : '' }} step="{{$step}}" max="{{$max}}" min="{{$min}}">
        </div>
        @error('message')
            <small class="text-danger" id="alert-error">{{$message}}</small>
        @enderror
    </div>
</div>
