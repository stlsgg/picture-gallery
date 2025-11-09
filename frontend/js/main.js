// include modules here
import { API_URL, DOM_IDS, INITIAL_STATE } from "./model/constants.js";
import { getDOMElements } from "./view/domElements.js";
import { checkAPI, fetchImages } from "./controller/api.js";
import {
  getFirstElement,
  getLastElement,
  getTotalPages,
} from "./controller/pagination.js";
import { form } from "./controller/uploadForm.js";
import { renderState } from "./view/renderState.js";

// загружаю constants
// DE - DOM Elements
const DE = getDOMElements(DOM_IDS);

// загружаю текующую страницу
// let currPage = INITIAL_STATE.currentPage || 1;
let currPage = 1;

async function loadPage(pageNum, itemsOnPage = 10) {
  // вычисляю пул картинок на страницу
  let firstEl = getFirstElement(pageNum, itemsOnPage);
  let lastEl = getLastElement(pageNum, itemsOnPage);
  renderState(DE["gallery"], {
    innerHTML: "<div class='loading loading-lg' id='loading-state'></div>",
    className: "gallery gallery-single",
  });

  // загружаю картинки из сервера
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

    renderState(DE["gallery"], {
      className: "gallery",
    });

    DE["gallery"].appendChild(card);
  });
  DE["gallery"].removeChild(document.getElementById("loading-state"));
}

function createCard(src, desc) {
  return `<div class="card"><div class="card-image"> <img src="${API_URL}/${src}"
alt="${desc}" class="img-responsive" > </div> <div class="card-body">
${desc} </div></div>`;
}

form();
loadPage(1);

// инициализация элемента с номерами страниц
// надо узнать общее количество страниц
// fetch всего meta.json и оценка, сколько там ключей
const meta = await fetch("http://api.gg.ru/api/images").then((res) =>
  res.json(),
);
const totalElements = Object.keys(meta.data).length; // допустим 16 элементов
const totalPages = getTotalPages(totalElements, 10); // общее количество
// страниц, округление вверх

// pagination элемент
const paginationHub = document.getElementById("pagination");
// clear pagination before creating new page elements
renderState(paginationHub, { innerHTML: "" });

for (let i = 1; i <= totalPages; i++) {
  const pageLink = document.createElement("li");
  renderState(pageLink, {
    className: currPage === i ? "c-hand page-item active" : "c-hand page-item",
  });

  const link = document.createElement("a");
  renderState(link, { innerText: i });

  link.addEventListener("click", () => {
    const links = paginationHub.querySelectorAll("li");
    links.forEach((element) =>
      renderState(element, { className: "c-hand page-item" }),
    );
    renderState(pageLink, { className: "c-hand page-item active" });
    loadPage(i);
  });

  pageLink.appendChild(link);
  paginationHub.appendChild(pageLink);
}
