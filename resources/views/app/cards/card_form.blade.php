@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endpush

<div class="row">
    <x-select col="12" set="" name="account_id" id="account_id" title="Banco vinculado">
        <option value="">Nenhum banco vinculado</option>
        @foreach($accounts as $account)
            <option value="{{$account->id}}">{{$account->bank_name}}</option>
        @endforeach
    </x-select>
</div>

<div class="row">
    <x-input col="12" set="" type="text" title="Titular" id="cardholder_name" name="cardholder_name" value="{{ old('cardholder_name', $card->cardholder_name ?? '') }}" placeholder="John Doe" disabled=""></x-input>
    <x-input col="12" set="" type="number" title="Últimos 4 dígitos" id="last_four_digits" name="last_four_digits" value="{{ old('last_four_digits', $card->last_four_digits ?? '') }}" placeholder="0766" disabled=""></x-input>
</div>

<div class="row">
    <x-select id="brand" name="brand" col="12" set="" title="Bandeira">
        <option value="1" data-image="https://cdn.simpleicons.org/visa">Visa</option>
        <option value="2" data-image="https://cdn.simpleicons.org/mastercard">Mastercard</option>
        <option value="3" data-image="https://cdn.simpleicons.org/americanexpress">American Express</option>
        <option value="4" data-image="https://cdn.simpleicons.org/discover">Discover</option>
        <option value="5" data-image="https://cdn.simpleicons.org/dinersclub">Diners Club</option>
        <option value="6" data-image="https://cdn.simpleicons.org/jcb">JCB</option>
        <option value="7" data-image="https://cdn.jsdelivr.net/npm/simple-icons@v15/icons/elo.svg">Elo</option>
    </x-select>
</div>

<div class="row">
    <x-input col="6" set="" type="color" title="Cor do cartão" id="color_card" name="color_card" value="{{ old('color_card', $card->color_card ?? '#000000') }}" disabled=""></x-input>
</div>

<div class="row">
    <x-input-price col="6" title="Valor" id="credit_limit" name="credit_limit"/>
</div>

<div class="row">
    <x-input col="6" set="" type="number" title="Fechamento" id="closing_day" name="closing_day" value="{{ old('closing_day', $card->closing_day ?? '') }}" disabled=""></x-input>
    <x-input col="6" set="" type="number" title="Vencimento" id="due_day" name="due_day" value="{{ old('due_day', $card->due_day ?? '') }}" disabled=""></x-input>
</div>

@push('scripts')
    <script src="{{asset('assets/js/common/mask_price_input.js')}}"></script>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(function(){
            $('#brand').select2({
                width: '100%',
                dropdownParent: $('#modalCard'),
                minimumResultsForSearch: Infinity,
                escapeMarkup: function(m) {
                    return m;
                },
                templateResult: function(opt) {
                    if (!opt.id) return opt.text;
                    const src = $(opt.element).data('image');
                    return `
        <span>
          <img src="${src}" style="width:20px;margin-right:8px;vertical-align:middle"/>
          ${opt.text}
        </span>`;
                },
                templateSelection: function(opt) {
                    if (!opt.id) return opt.text;
                    const src = $(opt.element).data('image');
                    return `
        <span>
          <img src="${src}" style="width:16px;margin-right:6px;vertical-align:middle"/>
          ${opt.text}
        </span>`;
                }
            });
        });
    </script>

@endpush
