<div class="col-{{$col}} offset-{{$set}}">
    <label for="{{$rangeInput}}" class="form-label text-muted" style="font-size:13.5px;font-weight:bold;letter-spacing:0.75px;">
        {{$title}}
    </label>

    <div class="d-flex align-items-center mb-2">
        <input
            type="number"
            id="{{$rangeInput}}-manual"
            class="form-control me-2"
            style="max-width:120px"
            min="{{$min}}"
            max="{{$max}}"
            step="0.01"
            value="{{$value}}"
        >
        <output id="{{$rangeValue}}" class="text-muted fw-bold"></output>
    </div>

    <input
        type="range"
        id="{{$rangeInput}}"
        class="form-range"
        min="{{$min}}"
        max="{{$max}}"
        step="0.01"
        value="{{$value}}"
        name="{{$name}}"
    >
</div>

<script>
    (function(){
        const slider = document.getElementById('{{$rangeInput}}');
        const manual = document.getElementById('{{$rangeInput}}-manual');
        const output = document.getElementById('{{$rangeValue}}');
        const fmt = new Intl.NumberFormat('pt-BR',{style:'currency',currency:'BRL'});

        function sync(val){
            const num = parseFloat(val) || 0;
            slider.value = num;
            manual.value = num.toFixed(2);
            output.textContent = fmt.format(num);
        }

        // inicializa
        sync(slider.value);

        slider.addEventListener('input', e=> sync(e.target.value));
        manual.addEventListener('input', e=> sync(e.target.value));
    })();
</script>
