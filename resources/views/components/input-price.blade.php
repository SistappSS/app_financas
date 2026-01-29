<div class="col-{{ $col }} offset-{{ $set }} mb-2">
    <div class="form-group">
        <label class="form-control-label text-muted" style="font-size: 13.5px; font-weight: bold; letter-spacing: 0.75px;" for="{{ $id }}">{{ $title }}</label>
        <div class="input-group">
            <input type="text" class="form-control form-control-sm moeda-brl" value="{{ $value ?? 0.00 }}" placeholder="{{ $placeholder ?? 'R$ 0,00'}}" {{ $disabled ? ' disabled' : '' }} autocomplete="off" inputmode="numeric" />
            <input type="number" class="moeda-brl-valor" name="{{ $name }}" id="{{ $id }}" step="0.01" hidden />
        </div>

        @error('message')
        <small class="text-danger" id="alert-error">{{$message}}</small>
        @enderror
    </div>
</div>
