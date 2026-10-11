import './bootstrap';
import Alpine from 'alpinejs';

window.Alpine = Alpine;

// Clipboard helper with visual toast / feedback
window.copyToClipboard = function (text, elementId = null) {
    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(text).then(() => {
            handleCopiedState(elementId);
        }).catch(err => {
            fallbackCopy(text, elementId);
        });
    } else {
        fallbackCopy(text, elementId);
    }
};

function fallbackCopy(text, elementId) {
    const textArea = document.createElement("textarea");
    textArea.value = text;
    textArea.style.position = "fixed";
    textArea.style.left = "-999999px";
    textArea.style.top = "-999999px";
    document.body.appendChild(textArea);
    textArea.focus();
    textArea.select();
    try {
        document.execCommand('copy');
        handleCopiedState(elementId);
    } catch (err) {
        console.error('Fallback copy failed', err);
    }
    textArea.remove();
}

function handleCopiedState(elementId) {
    if (!elementId) return;
    const el = document.getElementById(elementId);
    if (!el) return;

    const originalText = el.dataset.originalText || el.innerText;
    el.dataset.originalText = originalText;
    el.innerText = 'Copied ✓';
    el.classList.add('text-green-400', 'border-green-400');

    setTimeout(() => {
        el.innerText = originalText;
        el.classList.remove('text-green-400', 'border-green-400');
    }, 2200);
}

// Track event helper
window.trackConversionEvent = function (eventName, eventId = null, orderId = null, payload = {}) {
    // 1. Meta Pixel client event
    if (typeof fbq === 'function') {
        const standardEvents = ['PageView', 'ViewContent', 'InitiateCheckout', 'AddPaymentInfo', 'Purchase', 'Lead', 'Contact'];
        if (standardEvents.includes(eventName)) {
            if (eventId) {
                fbq('track', eventName, payload, { eventID: eventId });
            } else {
                fbq('track', eventName, payload);
            }
        } else {
            fbq('trackCustom', eventName, payload);
        }
    }

    // 2. Google Analytics 4 (if configured)
    if (typeof gtag === 'function') {
        gtag('event', eventName, payload);
    }

    // 3. Server logging
    try {
        fetch('/track-event', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            },
            body: JSON.stringify({
                event_name: eventName,
                event_id: eventId,
                order_id: orderId,
                payload: payload
            })
        }).catch(() => {});
    } catch (e) {}
};

Alpine.start();
