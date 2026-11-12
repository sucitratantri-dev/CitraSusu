<style>
#cs-ai-toggle {
 position:fixed;bottom:24px;right:24px;z-index:9998;
 border:0;border-radius:50px;background:#e9579b;color:white;
 padding:15px 22px;font-weight:700;cursor:pointer;
 box-shadow:0 5px 20px #e9579b55;
}
#cs-ai-window {
 display:none;position:fixed;bottom:90px;right:24px;
 width:min(370px,calc(100vw - 30px));height:510px;
 max-height:70vh;background:white;z-index:9999;
 border-radius:20px;box-shadow:0 8px 40px #0002;
 overflow:hidden;font-family:Arial,sans-serif;
 flex-direction:column;border:1px solid #f4d5e4;
}
#cs-ai-window.open {display:flex}
#cs-ai-header {
 background:linear-gradient(135deg,#e9579b,#f48cba);
 color:white;padding:17px;display:flex;
 justify-content:space-between;align-items:center;
}
#cs-ai-messages {
 flex:1;overflow-y:auto;padding:15px;
 background:#fffafd;display:flex;flex-direction:column;gap:12px;
}
.cs-ai-bubble {
 padding:11px 14px;border-radius:15px;max-width:85%;
 font-size:14px;line-height:1.5;white-space:pre-wrap;
 overflow-wrap:anywhere;
}
.cs-ai-user {align-self:flex-end;background:#fce2ef;color:#553044}
.cs-ai-bot {align-self:flex-start;background:#f1f1f1;color:#333}
#cs-ai-form {display:flex;gap:8px;padding:12px;border-top:1px solid #eee}
#cs-ai-input {
 flex:1;min-width:0;border:1px solid #ddd;border-radius:22px;
 padding:11px 14px;outline:none;
}
#cs-ai-send {
 background:#e9579b;color:white;border:0;border-radius:50%;
 width:42px;height:42px;cursor:pointer;font-size:17px;
}
#cs-ai-send:disabled {opacity:.5}
</style>

<button id="cs-ai-toggle" type="button">💬 Tanya CitraSusu AI</button>

<div id="cs-ai-window">
 <div id="cs-ai-header">
  <div><strong>💗 CitraSusu AI</strong><br>
   <small>Asisten virtual toko</small></div>
  <button id="cs-ai-close" type="button"
   style="background:none;border:0;color:white;font-size:23px;cursor:pointer">×</button>
 </div>
 <div id="cs-ai-messages">
  <div class="cs-ai-bubble cs-ai-bot">
   Halo Bunda! 👋 Selamat datang di Citra Susu. Ada yang bisa saya bantu?
  </div>
 </div>
 <form id="cs-ai-form">
  <input id="cs-ai-input" maxlength="1000"
   placeholder="Tulis pertanyaan..." autocomplete="off" required>
  <button id="cs-ai-send" type="submit">➤</button>
 </form>
</div>

<script>
(function () {
 const toggle = document.getElementById('cs-ai-toggle');
 const win = document.getElementById('cs-ai-window');
 const close = document.getElementById('cs-ai-close');
 const form = document.getElementById('cs-ai-form');
 const input = document.getElementById('cs-ai-input');
 const messages = document.getElementById('cs-ai-messages');
 const send = document.getElementById('cs-ai-send');

 if (!toggle || !win || !form) return;

 toggle.addEventListener('click', () => {
   win.classList.toggle('open');
   input.focus();
 });
 close.addEventListener('click', () => win.classList.remove('open'));

 function bubble(text, type) {
   const div = document.createElement('div');
   div.className = 'cs-ai-bubble ' + type;
   div.textContent = text;
   messages.appendChild(div);
   messages.scrollTop = messages.scrollHeight;
   return div;
 }

 form.addEventListener('submit', async function (event) {
   event.preventDefault();
   const message = input.value.trim();
   if (!message || send.disabled) return;

   bubble(message, 'cs-ai-user');
   input.value = '';
   send.disabled = true;
   const loading = bubble('CitraSusu AI sedang mengetik...', 'cs-ai-bot');

   try {
     const response = await fetch(@json(route('ai-chat.send')), {
       method: 'POST',
       headers: {
         'Content-Type': 'application/json',
         'Accept': 'application/json',
         'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
       },
       body: JSON.stringify({message: message})
     });

     const data = await response.json();
     loading.textContent = data.reply || 'Maaf, terjadi kesalahan.';
     if (!response.ok && !data.reply) {
       loading.textContent = 'Layanan AI sedang tidak tersedia.';
     }
   } catch (error) {
     loading.textContent = 'Koneksi bermasalah. Silakan coba lagi.';
   } finally {
     send.disabled = false;
     input.focus();
     messages.scrollTop = messages.scrollHeight;
   }
 });
})();
</script>