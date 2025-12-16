document.addEventListener('DOMContentLoaded', () => {
    const urlParams = new URLSearchParams(window.location.search);
    const uuid = urlParams.get('uuid');

    if (!uuid) {
        alert('Geçersiz Duvar Linki!');
        window.location.href = 'index.php';
        return;
    }

    const dom = {
        body: document.getElementById('wallBody'),
        title: document.getElementById('wallTitle'),
        countdown: document.getElementById('countdown'),
        container: document.getElementById('wall-container'),
        giftsLayer: document.getElementById('gifts-layer'),
        addModal: document.getElementById('addGiftModal'),
        viewModal: document.getElementById('viewGiftModal'),
        addBtn: document.getElementById('addGiftBtn'),
        closeAdd: document.getElementById('closeAddModal'),
        closeView: document.getElementById('closeViewModal'),
        form: document.getElementById('addGiftForm'),
        positionIndicator: document.getElementById('positionIndicator')
    };

    let eventTimeDate;
    let selectedPosition = null;
    let isWaitingForPosition = false;

    // Load Wall Data
    fetch(`api/get_wall.php?uuid=${uuid}`)
        .then(res => res.json())
        .then(data => {
            if (!data.success) {
                alert(data.message);
                window.location.href = 'index.php';
                return;
            }

            const wall = data.wall;
            dom.title.innerText = `${wall.creator_name}'in Duvarı`;
            dom.body.classList.add(`theme-${wall.event_type}`);

            // Populate gift options
            const selectEl = document.getElementById('gift_type');
            selectEl.innerHTML = '';

            if (data.available_gifts && data.available_gifts.length > 0) {
                data.available_gifts.forEach(g => {
                    const opt = document.createElement('option');
                    opt.value = g.icon;
                    opt.innerText = `${g.icon} ${g.name}`;
                    selectEl.appendChild(opt);
                });
            } else {
                const opt = document.createElement('option');
                opt.value = "🎁";
                opt.innerText = "🎁 Hediye";
                selectEl.appendChild(opt);
            }

            eventTimeDate = new Date(wall.event_time);
            startCountdown();

            // Render existing gifts
            data.gifts.forEach(gift => renderGift(gift));
        })
        .catch(err => console.error(err));

    function startCountdown() {
        setInterval(() => {
            const now = new Date();
            const diff = eventTimeDate - now;

            if (diff <= 0) {
                dom.countdown.innerText = "🎉 Açıldı!";
                return;
            }

            const days = Math.floor(diff / (1000 * 60 * 60 * 24));
            const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((diff % (1000 * 60)) / 1000);

            dom.countdown.innerText = `${days}g ${hours}s ${minutes}d ${seconds}sn`;
        }, 1000);
    }

    function renderGift(gift) {
        const el = document.createElement('div');
        el.className = 'gift-item';
        el.style.left = `${gift.position_x}%`;
        el.style.top = `${gift.position_y}%`;

        el.innerHTML = `
            <div class="gift-icon">${gift.gift_type}</div>
            <div class="gift-sender">${gift.sender_name}</div>
        `;

        el.addEventListener('click', (e) => {
            e.stopPropagation();
            document.getElementById('viewIcon').innerText = gift.gift_type;
            document.getElementById('viewSender').innerText = gift.sender_name;

            if (gift.is_locked) {
                document.getElementById('viewMessage').innerHTML = `<span class="locked-msg">🔒 ${gift.message}</span>`;
            } else {
                document.getElementById('viewMessage').innerText = gift.message;
            }

            dom.viewModal.classList.remove('hidden');
        });

        dom.giftsLayer.appendChild(el);
    }

    // Position Selection Logic
    if (dom.addBtn) {
        dom.addBtn.addEventListener('click', () => {
            isWaitingForPosition = true;
            dom.container.style.cursor = 'crosshair';
            dom.positionIndicator.classList.remove('hidden');
            alert('🎯 Duvarda hediyeni koymak istediğin yeri tıkla!');
        });
    }

    // Track mouse for position indicator
    dom.container.addEventListener('mousemove', (e) => {
        if (isWaitingForPosition) {
            const rect = dom.container.getBoundingClientRect();
            dom.positionIndicator.style.left = e.clientX + 'px';
            dom.positionIndicator.style.top = e.clientY + 'px';
        }
    });

    // Click to select position
    dom.container.addEventListener('click', (e) => {
        if (isWaitingForPosition) {
            const rect = dom.container.getBoundingClientRect();
            const x = ((e.clientX - rect.left) / rect.width) * 100;
            const y = ((e.clientY - rect.top) / rect.height) * 100;

            // Clamp values
            selectedPosition = {
                x: Math.max(5, Math.min(95, x)),
                y: Math.max(10, Math.min(90, y))
            };

            document.getElementById('position_x').value = selectedPosition.x;
            document.getElementById('position_y').value = selectedPosition.y;

            isWaitingForPosition = false;
            dom.container.style.cursor = '';
            dom.positionIndicator.classList.add('hidden');

            // Show form
            dom.addModal.classList.remove('hidden');
        }
    });

    // Modal Controls
    if (dom.closeAdd) {
        dom.closeAdd.addEventListener('click', () => {
            dom.addModal.classList.add('hidden');
            selectedPosition = null;
            document.getElementById('position_x').value = '';
            document.getElementById('position_y').value = '';
        });
    }

    if (dom.closeView) {
        dom.closeView.addEventListener('click', () => {
            dom.viewModal.classList.add('hidden');
        });
    }

    // Form Submit
    if (dom.form) {
        dom.form.addEventListener('submit', async (e) => {
            e.preventDefault();

            if (!selectedPosition) {
                alert('Lütfen duvarda bir konum seç!');
                return;
            }

            const formData = {
                wall_uuid: uuid,
                sender_name: document.getElementById('gift_sender').value.trim(),
                gift_type: document.getElementById('gift_type').value,
                message: document.getElementById('gift_message').value.trim(),
                position_x: parseInt(selectedPosition.x),
                position_y: parseInt(selectedPosition.y)
            };

            try {
                const res = await fetch('api/add_gift.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(formData)
                });
                const result = await res.json();

                if (result.success) {
                    alert('🎁 Hediyein eklendi! Teşekkürler!');
                    dom.addModal.classList.add('hidden');
                    dom.form.reset();
                    selectedPosition = null;
                    location.reload();
                } else {
                    alert('Hata: ' + result.message);
                }
            } catch (err) {
                console.error(err);
                alert('Bağlantı hatası!');
            }
        });
    }
});
