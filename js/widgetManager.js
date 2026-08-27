// Глобальный реестр виджетов
window.widgetManagers = {};

// Глобальный реестр загруженных JS файлов (чтобы не загружать повторно)
window.loadedJsFiles = window.loadedJsFiles || new Set();

// Функция для динамической загрузки JS файлов
function loadJsFile(filePath) {
    return new Promise((resolve, reject) => {
        // Проверяем, не загружен ли уже этот файл
        if (window.loadedJsFiles.has(filePath)) {
            resolve();
            return;
        }
        
        // Проверяем, не загружается ли уже этот файл
        const existingScript = document.querySelector(`script[src="${dirName}/${filePath}"]`);
        if (existingScript) {
            // Если скрипт уже загружен, помечаем его как загруженный
            if (existingScript.readyState === 'complete' || existingScript.readyState === 'loaded') {
                window.loadedJsFiles.add(filePath);
            }
            resolve();
            return;
        }
        
        const script = document.createElement('script');
        script.src = `${dirName}/${filePath}`;
        script.onload = () => {
            window.loadedJsFiles.add(filePath);
            resolve();
        };
        script.onerror = () => {
            reject(new Error(`Failed to load ${filePath}`));
        };
        document.head.appendChild(script);
    });
}

// Функция для загрузки массива JS файлов
async function loadJsFiles(jsFiles) {
    if (!jsFiles || !Array.isArray(jsFiles) || jsFiles.length === 0) {
        return;
    }
    
    const promises = jsFiles.map(file => loadJsFile(file));
    await Promise.all(promises);
}

class WidgetManager {
    constructor(widgetId, element) {
        this.widgetId = widgetId;
        this.element = element;
        this.state = WidgetUtils.getWidgetState(widgetId);
        this.loading = false;
        this.contentLoaded = false; // Флаг первой загрузки контента
    }
    
    // Загрузить данные виджета
    async load() {
        if (this.loading) {
            return;
        }
        this.loading = true;
        this.showLoading();
        
        try {
            // Получаем parent_id из атрибута виджета (для вложенных виджетов внутри строк таблицы)
            const parentId = this.element ? this.element.getAttribute('data-parent-id') : null;
            let url = `${dirName}/?widgetAction=load&widgetId=${this.widgetId}&dataType=json`;
            if (parentId) {
                url += `&parent_id=${encodeURIComponent(parentId)}`;
            }
            const response = await fetch(url, {
                method: 'POST',
                headers: {'Content-Type': 'application/json'}
            });
            const data = await response.json();
            if (data.html) {
                // Загружаем JS файлы, если они есть в ответе
                if (data.jsFiles && data.jsFiles.length > 0) {
                    await loadJsFiles(data.jsFiles);
                }
                this.updateView(data.html);
                // Инициализируем вложенные виджеты только при первой загрузке
                if (!this.contentLoaded) {
                    // НЕ инициализируем вложенные виджеты при обновлении - они уже инициализированы
                    this.contentLoaded = true;
                }
            } else if (data.error) {
                this.showError(data.error);
            }
        } catch (error) {
            this.showError(error.message || 'Ошибка загрузки');
        } finally {
            this.loading = false;
        }
    }
    
    // Установить страницу
    async setPage(page) {
        if (this.loading) return;
        this.loading = true;
        this.showLoading();
        
        try {
            const parentId = this.element ? this.element.getAttribute('data-parent-id') : null;
            let url = `${dirName}/?widgetAction=pagination&widgetId=${this.widgetId}&dataType=json`;
            if (parentId) {
                url += `&parent_id=${encodeURIComponent(parentId)}`;
            }
            
            const formData = new FormData();
            formData.append('page', page);
            
            const response = await fetch(url, {
                method: 'POST',
                body: formData
            });
            const data = await response.json();
            if (data.html) {
                // Загружаем JS файлы, если они есть в ответе
                if (data.jsFiles && data.jsFiles.length > 0) {
                    await loadJsFiles(data.jsFiles);
                }
                this.updateView(data.html);
                // НЕ инициализируем вложенные виджеты - они уже инициализированы
            } else if (data.error) {
                this.showError(data.error);
            }
        } catch (error) {
            this.showError(error.message || 'Ошибка пагинации');
        } finally {
            this.loading = false;
        }
    }
    
    // Установить фильтр
    async setFilter(name, value) {
        if (this.loading) return;
        this.loading = true;
        this.showLoading();
        
        try {
            const formData = new FormData();
            formData.append('element', name);
            if (value === null) {
                formData.append('clearFilter', '1');
            } else {
                if (Array.isArray(value)) {
                    value.forEach(v => formData.append('data[]', v));
                } else {
                    formData.append('data', value);
                }
            }
            
            const parentId = this.element ? this.element.getAttribute('data-parent-id') : null;
            let url = `${dirName}/?widgetAction=filter&widgetId=${this.widgetId}&dataType=json`;
            if (parentId) {
                url += `&parent_id=${encodeURIComponent(parentId)}`;
            }
            
            const response = await fetch(url, {
                method: 'POST',
                body: formData
            });
            const data = await response.json();
            if (data.html) {
                // Загружаем JS файлы, если они есть в ответе
                if (data.jsFiles && data.jsFiles.length > 0) {
                    await loadJsFiles(data.jsFiles);
                }
                this.updateView(data.html);
                // НЕ инициализируем вложенные виджеты при обновлении - они уже инициализированы
            } else if (data.error) {
                this.showError(data.error);
            }
        } catch (error) {
            this.showError(error.message || 'Ошибка применения фильтра');
        } finally {
            this.loading = false;
        }
    }
    
    // Установить фронтальный фильтр
    async setFrontFilter(name, value) {
        if (this.loading) return;
        this.loading = true;
        this.showLoading();
        
        try {
            const formData = new FormData();
            formData.append(`${name}FrontFilter`, value);
            
            const parentId = this.element ? this.element.getAttribute('data-parent-id') : null;
            let url = `${dirName}/?widgetAction=frontFilter&widgetId=${this.widgetId}&dataType=json`;
            if (parentId) {
                url += `&parent_id=${encodeURIComponent(parentId)}`;
            }
            
            const response = await fetch(url, {
                method: 'POST',
                body: formData
            });
            const data = await response.json();
            if (data.html) {
                // Загружаем JS файлы, если они есть в ответе
                if (data.jsFiles && data.jsFiles.length > 0) {
                    await loadJsFiles(data.jsFiles);
                }
                this.updateView(data.html);
                // НЕ инициализируем вложенные виджеты при обновлении - они уже инициализированы
            } else if (data.error) {
                this.showError(data.error);
            }
        } catch (error) {
            this.showError(error.message || 'Ошибка применения фильтра');
        } finally {
            this.loading = false;
        }
    }
    
    // Установить поиск
    async setSearch(query) {
        if (this.loading) return;
        this.loading = true;
        this.showLoading();
        
        try {
            const formData = new FormData();
            formData.append('textsearch', query || '');
            
            const parentId = this.element ? this.element.getAttribute('data-parent-id') : null;
            let url = `${dirName}/?widgetAction=search&widgetId=${this.widgetId}&dataType=json`;
            if (parentId) {
                url += `&parent_id=${encodeURIComponent(parentId)}`;
            }
            
            const response = await fetch(url, {
                method: 'POST',
                body: formData
            });
            const data = await response.json();
            if (data.html) {
                // Загружаем JS файлы, если они есть в ответе
                if (data.jsFiles && data.jsFiles.length > 0) {
                    await loadJsFiles(data.jsFiles);
                }
                this.updateView(data.html);
                // НЕ инициализируем вложенные виджеты при обновлении - они уже инициализированы
            } else if (data.error) {
                this.showError(data.error);
            }
        } catch (error) {
            this.showError(error.message || 'Ошибка поиска');
        } finally {
            this.loading = false;
        }
    }
    
    // Обновить вид
    updateView(html) {
        const container = document.querySelector(`[data-widget-id="${this.widgetId}"]`);
        if (container) {
            // Сохраняем значения полей формы перед обновлением
            const savedValues = {};
            const searchInput = container.querySelector('input[name="textsearch"]');
            if (searchInput) {
                savedValues.textsearch = searchInput.value;
            }
            // Сохраняем значения фронтальных фильтров
            const frontFilterInputs = container.querySelectorAll('form[data-front-filter] input, form[data-front-filter] select');
            frontFilterInputs.forEach(input => {
                if (input.name && input.name !== 'parentElement' && input.name !== 'component') {
                    savedValues[input.name] = input.value;
                }
            });
            
            // HTML может содержать обертку col-md-12, нужно извлечь только содержимое
            // Создаем временный элемент для парсинга HTML
            const tempDiv = document.createElement('div');
            tempDiv.innerHTML = html.trim();
            
            let contentHtml = html;
            
            // Проверяем, есть ли обертка <div class="col-md-...">
            const colWrapper = tempDiv.querySelector('[class*="col-md-"]');
            if (colWrapper) {
                // Извлекаем содержимое обертки
                contentHtml = colWrapper.innerHTML;
            } else {
                // Проверяем, есть ли виджет-контейнер с нашим widgetId
                const widgetInHtml = tempDiv.querySelector(`[data-widget-id="${this.widgetId}"]`);
                if (widgetInHtml) {
                    contentHtml = widgetInHtml.innerHTML;
                } else {
                    // Используем HTML как есть
                    contentHtml = html;
                }
            }
            
            // Заменяем содержимое контейнера (но не сам контейнер)
            container.innerHTML = contentHtml;
            container.classList.remove('widget-loading-state');
            // Убираем спиннер если остался
            const loadingEl = container.querySelector('.widget-loading');
            if (loadingEl) {
                loadingEl.remove();
            }
            
            // Восстанавливаем сохраненные значения полей формы
            if (savedValues.textsearch !== undefined) {
                const newSearchInput = container.querySelector('input[name="textsearch"]');
                if (newSearchInput) {
                    newSearchInput.value = savedValues.textsearch;
                }
            }
            // Восстанавливаем значения фронтальных фильтров
            Object.keys(savedValues).forEach(name => {
                if (name !== 'textsearch') {
                    const input = container.querySelector(`[name="${name}"]`);
                    if (input) {
                        input.value = savedValues[name];
                    }
                }
            });
            
            // Сохраняем data-parent-id из нового контента, если он есть
            // Или сохраняем существующий, если он был до обновления
            const existingParentId = container.getAttribute('data-parent-id');
            const newWidgetContainer = container.querySelector(`[data-widget-id="${this.widgetId}"]`);
            if (newWidgetContainer && newWidgetContainer.hasAttribute('data-parent-id')) {
                const newParentId = newWidgetContainer.getAttribute('data-parent-id');
                container.setAttribute('data-parent-id', newParentId);
                // Обновляем this.element для последующих запросов
                this.element = container;
            } else if (existingParentId) {
                // Если в новом контенте нет атрибута, но был до обновления - сохраняем его
                container.setAttribute('data-parent-id', existingParentId);
            }
            
            this.bindEvents(); // Привязать события
            // Переинициализировать feather icons если нужно
            if (typeof feather !== 'undefined') {
                feather.replace();
            }
            // НЕ инициализируем вложенные виджеты при обновлении контента
            // Они уже должны быть инициализированы при первой загрузке
        }
    }
    
    // Показать загрузку
    showLoading() {
        const container = document.querySelector(`[data-widget-id="${this.widgetId}"]`);
        if (container) {
            // Проверяем, есть ли уже спиннер в placeholder
            let loadingEl = container.querySelector('.widget-loading');
            if (!loadingEl) {
                // Если спиннера нет, создаем его поверх контента
                loadingEl = document.createElement('div');
                loadingEl.className = 'widget-loading';
                loadingEl.innerHTML = '<div class="spinner-border text-primary" role="status"><span class="sr-only">Загрузка...</span></div>';
                container.appendChild(loadingEl);
            } else {
                // Показываем существующий спиннер
                loadingEl.style.display = 'flex';
            }
            container.classList.add('widget-loading-state');
        }
    }
    
    // Показать ошибку
    showError(error) {
        const container = document.querySelector(`[data-widget-id="${this.widgetId}"]`);
        if (container) {
            container.innerHTML = `<div class="widget-error">Ошибка загрузки: ${error}</div>`;
        }
    }
    
    // Привязать события
    bindEvents() {
        const container = this.element || document.querySelector(`[data-widget-id="${this.widgetId}"]`);
        if (!container) return;
        
        // Пагинация - используем делегирование с высоким приоритетом
        // Отключаем старые обработчики и устанавливаем новый с более высоким приоритетом
        // Используем namespace 'widget' и привязываем к контейнеру, чтобы сработать раньше обработчика на document
        $(container).off('click.widget', '.pager').on('click.widget', '.pager', (e) => {
            // Останавливаем все всплытия сразу
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();
            
            // Получаем страницу из атрибута
            const $target = $(e.target);
            let page = parseInt($target.attr('page'));
            if (!page || isNaN(page)) {
                // Если клик был по тексту внутри ссылки, ищем родительскую ссылку
                const $pager = $target.closest('.pager');
                page = parseInt($pager.attr('page'));
            }
            
            if (page && !isNaN(page)) {
                this.setPage(page);
            }
            return false;
        });
        
        // Текстовый поиск
        $(container).find('form[data-textsearch]').off('submit.widget').on('submit.widget', (e) => {
            e.preventDefault();
            const query = $(e.target).find('input[name="textsearch"]').val();
            this.setSearch(query || '');
        });
        
        // Фронтальные фильтры
        $(container).find('form[data-front-filter]').off('submit.widget').on('submit.widget', (e) => {
            e.preventDefault();
            if (this.loading) return;
            this.loading = true;
            this.showLoading();
            
                   const parentId = this.element ? this.element.getAttribute('data-parent-id') : null;
                   let url = `${dirName}/?widgetAction=frontFilter&widgetId=${this.widgetId}&dataType=json`;
                   if (parentId) {
                       url += `&parent_id=${encodeURIComponent(parentId)}`;
                   }
                   
                   const formData = new FormData(e.target);
                   // Отправляем форму как есть - сервер сам разберет FrontFilter поля
                   fetch(url, {
                       method: 'POST',
                       body: formData
                   })
            .then(response => response.json())
            .then(async data => {
                if (data.html) {
                    // Загружаем JS файлы, если они есть в ответе
                    if (data.jsFiles && data.jsFiles.length > 0) {
                        await loadJsFiles(data.jsFiles);
                    }
                    this.updateView(data.html);
                    // НЕ инициализируем вложенные виджеты при обновлении - они уже инициализированы
                } else if (data.error) {
                    this.showError(data.error);
                }
            })
            .catch(error => {
                this.showError(error.message || 'Ошибка применения фильтра');
            })
            .finally(() => {
                this.loading = false;
            });
        });
        
        // Фильтры через модальное окно - обрабатываются через существующий код в core.js
        // Но нужно перехватить submit формы фильтра
        $(container).find('form[action*="filter"]').off('submit.widget').on('submit.widget', (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            const filterName = formData.get('element');
            const filterData = formData.get('data');
            const clearFilter = formData.get('clearFilter');
            
            if (clearFilter) {
                this.setFilter(filterName, null);
            } else if (filterName && filterData !== null) {
                // filterData может быть массивом для multiselect
                const value = formData.getAll('data[]').length > 0 ? formData.getAll('data[]') : filterData;
                this.setFilter(filterName, value);
            }
        });
        
        // Массовые действия - будет обрабатываться через существующий код
    }
    
    // Инициализировать вложенные виджеты
    initNestedWidgets() {
        const container = this.element || document.querySelector(`[data-widget-id="${this.widgetId}"]`);
        if (!container) return;
        
        // Ищем только прямых потомков с data-widget-id, не включая сам контейнер
        const nestedWidgets = $(container).find('[data-widget-id]').not(container);
        nestedWidgets.each((index, el) => {
            const widgetId = $(el).attr('data-widget-id');
            // Проверяем, что это действительно вложенный виджет (не тот же самый)
            // И проверяем, что widgetId валидный (не содержит дублирующихся элементов)
            if (widgetId && widgetId !== this.widgetId && !window.widgetManagers[widgetId]) {
                // Проверяем валидность widgetId - он должен иметь правильный формат
                if (widgetId.match(/^widget_[A-Za-z0-9+\/]+=*_[a-f0-9]{8}$/)) {
                    window.widgetManagers[widgetId] = new WidgetManager(widgetId, el);
                    window.widgetManagers[widgetId].load();
                }
            }
        });
    }
}

// Автоинициализация при загрузке страницы
$(document).ready(() => {
    // Загрузить widgetUtils.js если еще не загружен
    if (typeof WidgetUtils === 'undefined') {
        return;
    }
    
    // Функция инициализации виджетов
    function initWidgets() {
        // Найти все виджеты на странице и инициализировать их
        const widgets = $('[data-widget-id]');
        
        widgets.each((index, el) => {
            const widgetId = $(el).attr('data-widget-id');
            if (widgetId && !window.widgetManagers[widgetId]) {
                window.widgetManagers[widgetId] = new WidgetManager(widgetId, el);
                window.widgetManagers[widgetId].bindEvents();
                
                // Загружаем контент только если виджет показывает placeholder
                const $widget = $(el);
                const hasOnlyPlaceholder = $widget.find('.widget-loading').length > 0 && $widget.find('table').length === 0;
                if (hasOnlyPlaceholder) {
                    window.widgetManagers[widgetId].load();
                }
            }
        });
    }
    
    // Инициализация сразу при готовности DOM
    initWidgets();
    
    // Также инициализируем вложенные виджеты, которые могут появиться после загрузки
    // Используем несколько попыток с разными задержками
    setTimeout(() => {
        initWidgets();
    }, 100);
    
    setTimeout(() => {
        initWidgets();
    }, 500);
    
    // Используем MutationObserver для отслеживания динамически добавляемых виджетов
    if (typeof MutationObserver !== 'undefined') {
        const observer = new MutationObserver((mutations) => {
            let shouldCheck = false;
            mutations.forEach((mutation) => {
                mutation.addedNodes.forEach((node) => {
                    if (node.nodeType === 1) { // Element node
                        if (node.hasAttribute && node.hasAttribute('data-widget-id')) {
                            shouldCheck = true;
                        } else if (node.querySelector && node.querySelector('[data-widget-id]')) {
                            shouldCheck = true;
                        }
                    }
                });
            });
            if (shouldCheck) {
                initWidgets();
            }
        });
        
        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    }
});
