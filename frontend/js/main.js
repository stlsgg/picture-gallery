// include modules here
import { API_URL, DOM_IDS, INITIAL_STATE } from "./model/constants.js";
import { getDOMElements } from "./view/domElements.js";
import { checkAPI, fetchImages } from "./controller/api.js";
import { getFirstElement, getLastElement } from "./controller/pagination.js";
import { form } from "./controller/uploadForm.js";

// здесь соединить controller, model, view functions воедино
// загружаем галерею:
// загрузка локального хранилища (текущая страница)
// fetch фоток с api, инициализация элементов.

// загружаю constants
// DE - DOM Elements
const DE = getDOMElements(DOM_IDS);

// загружаю текующую страницу
// let currPage = INITIAL_STATE.currentPage || 1;
let currPage = 3;

// загружаю картинки из сервера
// вычисляю пул картинок на страницу
const itemsOnPage = 6;
let firstEl = getFirstElement(currPage, itemsOnPage);
let lastEl = getLastElement(currPage, itemsOnPage);

// получаю данные о картинках
// быстрый чек доступности
if (!checkAPI(API_URL)) console.error("api not working!");

const images = await fetchImages(firstEl, lastEl, API_URL);

images.forEach((image, idx) => {
  // загружаю на страницу
  const card = document.createElement("div");
  card.innerHTML = createCard(image?.thumb, image?.desc || "без описания!");

  card.addEventListener("click", () => {
    const modalFullImageElem = document.getElementById(
      "modal-full-image-window",
    );

    const loading = document.createElement("div");
    loading.className = "loading loading-lg";
    const modalFullImage = modalFullImageElem.querySelector("img");
    modalFullImage.replaceWith(loading);

    const newImage = document.createElement("img");
    newImage.className = "img-responsive";
    const desc = modalFullImageElem.querySelector(".description");
    newImage.src = `${API_URL}/${images[idx]?.full}`;
    newImage.alt = images[idx]?.desc || "нет описания, пипяу ;D";
    desc.textContent = images[idx]?.desc || "нет описания, пипяу ;D";

    loading.replaceWith(newImage);
    window.location.href = "#modal-full-image-window";
  });

  DE["gallery"].appendChild(card);
});

function createCard(src, desc) {
  return `<div class="card"><div class="card-image"> <img src="${API_URL}/${src}"
alt="${desc}" class="img-responsive" > </div> <div class="card-body">
${desc} </div></div>`;
}

form();
