window.store = {
    set(k, v, ttlSec=60){ const exp=Date.now()+ttlSec*1000; sessionStorage.setItem(k, JSON.stringify({exp,v})); },
    get(k){ const raw=sessionStorage.getItem(k); if(!raw) return null;
        try{ const {exp,v}=JSON.parse(raw); if(Date.now()>exp){ sessionStorage.removeItem(k); return null; } return v; }catch{ return null;}
    }
};
