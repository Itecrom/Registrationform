document.addEventListener('DOMContentLoaded', () => {
    const sections = document.querySelectorAll('.form-section');
    const nextBtns = document.querySelectorAll('.next');
    const prevBtns = document.querySelectorAll('.prev');
    const progress = document.querySelector('.progress');
    let current = 0;

    sections[current].classList.add('active');
    updateProgress();

    nextBtns.forEach(btn => btn.addEventListener('click', () => {
        const inputs = sections[current].querySelectorAll('input,textarea,select');
        for (let i of inputs){
            if(!i.checkValidity()){
                alert('Please fill all required fields correctly.'); return;
            }
        }
        sections[current].classList.remove('active');
        current = Math.min(current+1,sections.length-1);
        sections[current].classList.add('active');
        updateProgress();
    }));

    prevBtns.forEach(btn => btn.addEventListener('click', () => {
        sections[current].classList.remove('active');
        current = Math.max(current-1,0);
        sections[current].classList.add('active');
        updateProgress();
    }));

    function updateProgress(){
        let percent = (current/(sections.length-1))*100;
        progress.style.width = percent+'%';
    }

    // File Validation
    const proof = document.getElementById('proof');
    const fileError = document.getElementById('fileError');
    proof.addEventListener('change', ()=>{
        fileError.textContent='';
        const allowed=['jpg','jpeg','png','pdf'];
        const file=proof.files[0];
        if(!file) return;
        const ext=file.name.split('.').pop().toLowerCase();
        if(!allowed.includes(ext)) fileError.textContent='Invalid file type!';
        else if(file.size>5*1024*1024) fileError.textContent='File too large!';
    });
});
