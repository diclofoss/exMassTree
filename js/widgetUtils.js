class WidgetUtils {
    // Получить состояние виджета из cookies
    static getWidgetState(widgetId, defaultValue = null) {
        const cookieName = widgetId + '_state';
        const cookies = document.cookie.split(';');
        for (let cookie of cookies) {
            const [name, value] = cookie.trim().split('=');
            if (name === cookieName) {
                try {
                    return JSON.parse(decodeURIComponent(value));
                } catch (e) {
                    return defaultValue;
                }
            }
        }
        return defaultValue || {page: 1, filters: {}, frontFilters: {}, textsearch: ''};
    }
    
    // Сохранить состояние виджета в cookies
    static setWidgetState(widgetId, state) {
        const cookieName = widgetId + '_state';
        const value = encodeURIComponent(JSON.stringify(state));
        const expires = new Date();
        expires.setTime(expires.getTime() + (30 * 24 * 60 * 60 * 1000)); // 30 дней
        document.cookie = `${cookieName}=${value};expires=${expires.toUTCString()};path=/`;
    }
    
    // Получить значение из состояния
    static getWidgetCookie(widgetId, key, defaultValue = null) {
        const state = this.getWidgetState(widgetId);
        return state[key] !== undefined ? state[key] : defaultValue;
    }
    
    // Установить значение в состоянии
    static setWidgetCookie(widgetId, key, value) {
        const state = this.getWidgetState(widgetId);
        state[key] = value;
        this.setWidgetState(widgetId, state);
    }
    
    // Парсинг widgetId для получения пути
    static parseWidgetId(widgetId) {
        // widget_category_component_element1_element2_hash
        const match = widgetId.match(/^widget_(.+)_[a-f0-9]{8}$/);
        if (match) {
            const pathParts = match[1].split('_');
            if (pathParts.length >= 2) {
                return {
                    category: pathParts[0],
                    component: pathParts[1],
                    elementPath: pathParts.slice(2)
                };
            }
        }
        return null;
    }
}
