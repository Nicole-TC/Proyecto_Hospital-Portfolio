    const switchDarkMode = document.getElementById('darkModeSwitch');
    switchDarkMode.addEventListener('change', () => {
      document.body.classList.toggle('modo-oscuro', switchDarkMode.checked);
      document.body.classList.toggle('modo-claro', !switchDarkMode.checked);
    });