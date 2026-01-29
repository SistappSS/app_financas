<ul class="swipe-list">
    @for ($i=0;$i<6;$i++)
        <li class="swipe-item">
            <div class="swipe-content">
                <div class="tx-line">
                    <div class="d-flex flex-column" style="gap:6px;min-width:55%">
                        <div class="shimmer is-loading" style="height:14px;border-radius:6px;"></div>
                        <div class="shimmer is-loading" style="height:10px;width:60%;border-radius:6px;"></div>
                    </div>
                    <div class="text-end" style="min-width:80px">
                        <div class="shimmer is-loading" style="height:12px;width:80px;border-radius:6px;"></div>
                        <div class="shimmer is-loading" style="height:10px;width:54px;margin-top:6px;border-radius:6px;"></div>
                    </div>
                </div>
            </div>
        </li>
    @endfor
</ul>
