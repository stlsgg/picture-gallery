// drag and drop feature
const drop_zone = document.getElementById("drop-zone");
const file_input = document.getElementById("file-upload");

drop_zone.addEventListener("dragover", (e) => {
  e.preventDefault();
  drop_zone.classList.add("active");
});

drop_zone.addEventListener("dragleave", (e) => {
  e.preventDefault();
  drop_zone.classList.remove("active");
});

drop_zone.addEventListener("drop", (e) => {
  e.preventDefault();
  drop_zone.classList.remove("active");

  const file = e.dataTransfer.files[0];
  file_input.files = e.dataTransfer.files;

  const label = drop_zone.querySelector("label");
  label.innerText = file.name;
});

file_input.addEventListener("change", () => {
  if (file_input.files.length > 0) {
    const label = drop_zone.querySelector("label");
    label.innerText = file_input.files[0].name;
  } else {
    const label = drop_zone.querySelector("label");
    label.innerText = "Перетащите сюда картинку :3";
  }
});

// upload form, AJAX upload
const formElem = document.getElementById("upload-form");
const submitBtn = document.getElementById("submit-btn");

submitBtn.addEventListener("click", async () => {
  const formContents = new FormData(formElem);

  const response = await fetch(formElem.action, {
    method: "POST",
    body: formContents,
  });

  const result = await response.json();
  console.log(result);
});
