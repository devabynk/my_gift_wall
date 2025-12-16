function nextStep(currentStep) {
    // Validation
    if (currentStep === 1) {
        const name = document.getElementById('creator_name').value.trim();
        if (!name) { alert('Lütfen adını yaz!'); return; }
    }
    else if (currentStep === 2) {
        const theme = document.getElementById('event_type').value;
        if (!theme) { alert('Lütfen bir konsept seç!'); return; }
    }

    // Hide Current
    document.getElementById(`step${currentStep}`).classList.remove('active');
    document.querySelector(`.step-dot[data-step="${currentStep}"]`).classList.remove('active');
    document.querySelector(`.step-dot[data-step="${currentStep}"]`).classList.add('completed'); // Optional style

    // Show Next
    const next = currentStep + 1;
    document.getElementById(`step${next}`).classList.add('active');
    document.querySelector(`.step-dot[data-step="${next}"]`).classList.add('active');
}

function prevStep(currentStep) {
    document.getElementById(`step${currentStep}`).classList.remove('active');
    document.querySelector(`.step-dot[data-step="${currentStep}"]`).classList.remove('active');

    const prev = currentStep - 1;
    document.getElementById(`step${prev}`).classList.add('active');
    document.querySelector(`.step-dot[data-step="${prev}"]`).classList.add('active');
}

function selectTheme(card, slug) {
    // UI Selection
    document.querySelectorAll('.theme-option-card').forEach(el => el.classList.remove('selected'));
    card.classList.add('selected');

    // Set Value
    document.getElementById('event_type').value = slug;
}

async function submitWizard() {
    const time = document.getElementById('event_time').value;
    if (!time) { alert('Lütfen tarihi seç!'); return; }

    const formData = {
        creator_name: document.getElementById('creator_name').value,
        event_type: document.getElementById('event_type').value,
        event_time: time
    };

    try {
        const response = await fetch('api/create_wall.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(formData)
        });
        const result = await response.json();

        if (result.success) {
            const link = `${window.location.origin}${window.location.pathname.replace('create.php', '')}wall.php?uuid=${result.wall_uuid}`;
            document.getElementById('shareLink').value = link;
            document.getElementById('viewWallBtn').href = link;
            document.getElementById('resultModal').classList.remove('hidden');
        } else {
            alert('Hata: ' + result.message);
        }
    } catch (err) {
        console.error(err);
        alert('Bir hata oluştu.');
    }
}

function shareWhatsapp() {
    const link = document.getElementById('shareLink').value;
    const text = `Sana bir sürprizim var! Buraya bir not bırakır mısın? ${link}`;
    window.open(`https://wa.me/?text=${encodeURIComponent(text)}`, '_blank');
}

document.getElementById('copyBtn').addEventListener('click', () => {
    const copyText = document.getElementById("shareLink");
    copyText.select();
    document.execCommand("copy");
    document.getElementById('copyBtn').innerText = "Kopyalandı!";
});
