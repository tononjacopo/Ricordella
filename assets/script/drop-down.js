    const dropdowns = document.querySelectorAll('.dropdown');

//Loop through all dropdown elements
dropdowns.forEach(dropdown => {
  //Get inner elements from each dropdown
  const select = dropdown.querySelector('.select');
  const caret = dropdown.querySelector('.caret');
  const menu = dropdown.querySelector('.menu');
  const options = dropdown.querySelectorAll('.menu li');
  const selected = dropdown.querySelector('.selected');

  /*
    We are using this method in order to have
    multiple dropdown menus on the page work
  */

  /*=== Toggle select element classes on click ===*/
  //Add a click event to the select element
  select.addEventListener('click', () => {
    //Add the clicked select styles to the select element
    select.classList.toggle('select-clicked');
    //Add the rotate styles to the caret element
    caret.classList.toggle('caret-rotate');
    //Add the open styles to the menu element
    menu.classList.toggle('menu-open');
  });

  /*=== Toggle option, selected and menu element classes on option click ===*/
  //Loop through all option elements
  options.forEach(option => {
    //Add a click event to the option element
    option.addEventListener('click', () => {

      const hiddenInput = document.getElementById('is_premium_input');
      if (hiddenInput) {
        hiddenInput.value = option.getAttribute('data-value');
      }

      //Change selected inner text to clicked option inner text
      selected.innerText = option.innerText;
      //Add text fade in animation
      selected.classList.add("text-fade-in");
      //Remove animation after it is finished (so that it can work again)
      setTimeout(() => {
        selected.classList.remove("text-fade-in");
      }, 300);
      //Remove the clicked select styles from the select element
      select.classList.remove('select-clicked');
      //Remove the rotate styles from the caret element
      caret.classList.remove('caret-rotate');
      //Remove the open styles from the menu element
      menu.classList.remove('menu-open');
      //Remove active class from all option elements
      options.forEach(option => {
        option.classList.remove('active');
      });
      //Add active class to clicked option element
      option.classList.add('active');
    });
  });

  /*=== Click outside to close functionality*/
  //Add click event to the entire window
  window.addEventListener("click", e => {
    //Get the dropdown size and position
    const size = dropdown.getBoundingClientRect();
    /*If the click is outside of the dropdown,
    also close the dropdown*/
    if(
      e.clientX < size.left ||
      e.clientX > size.right ||
      e.clientY < size.top ||
      e.clientY > size.bottom
    ) {
      //Remove the clicked select styles from the select element
      select.classList.remove('select-clicked');
      //Remove the rotate styles from the caret element
      caret.classList.remove('caret-rotate');
      //Remove the open styles from the menu element
      menu.classList.remove('menu-open');
    }
  });
});