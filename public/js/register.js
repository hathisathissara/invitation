let currentStep = 1;

function goStep(n) {
    if (n > currentStep) {
        if (currentStep === 1) {
            const bride = document.getElementById('bride_name').value.trim();
            const groom = document.getElementById('groom_name').value.trim();
            if (!bride || !groom) { alert('Please enter both names.'); return; }
        }
        if (currentStep === 2) {
            const date  = document.getElementById('wedding_date').value;
            const venue = document.getElementById('venue').value.trim();
            if (!date || !venue) { alert('Please fill in the wedding date and venue.'); return; }
        }
        if (currentStep === 3) {
            const pw  = document.getElementById('password').value;
            const cpw = document.getElementById('password_confirmation').value;
            if (pw !== cpw) { alert('Passwords do not match.'); return; }
        }
    }

    document.getElementById('step-' + currentStep).classList.remove('active');
    document.getElementById('step-' + n).classList.add('active');

    for (let i = 1; i <= 3; i++) {
        const c = document.getElementById('s' + i + 'c');
        c.classList.remove('active', 'done');
        if (i < n) { c.classList.add('done'); c.innerHTML = '<i class="fas fa-check" style="font-size:0.75rem;"></i>'; }
        else if (i === n) { c.classList.add('active'); c.textContent = i; }
        else { c.textContent = i; }
    }

    currentStep = n;
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

document.addEventListener('DOMContentLoaded', function() {
    
    // Validate form on submit
    const regForm = document.getElementById('reg-form');
    if(regForm) {
        regForm.addEventListener('submit', function(e) {
            const pw  = document.getElementById('password').value;
            const cpw = document.getElementById('password_confirmation').value;
            const recaptcha = document.querySelector('.g-recaptcha-response');
            
            if (pw !== cpw) {
                e.preventDefault();
                alert('Passwords do not match!');
                return;
            }
            
            if (!recaptcha || !recaptcha.value) {
                e.preventDefault();
                alert('Please confirm you are not a robot.');
                return;
            }
        });
    }

    // Check if there are validation errors (Laravel adds .error-box div if errors exist)
    if (document.querySelector('.error-box')) {
        goStep(3); // Auto jump to step 3 to show errors
    }
});