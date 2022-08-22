import Axios from 'axios'
//---------------import { store } from './store.js'
//---------------import { Message } from 'element-ui'

export const Util = {
  //---------------app_version: APP_VERSION,
  vm: null,
  /**
   * отправка запросов на сервер
   */
  http: {
    /**
     * http base URL
     */
    //---------------base: '/v2',
    /**
     * Отправка данных на сервер GET
     * @param {Object} params
     * @param {function} [callbackSuccess=null]
     * @param {function} [callbackError=null]
     * @param {function} [callbackFinally=null]
     */
    get: function (params, callbackSuccess, callbackError, callbackFinally) {
      return this.ajax('get', params, callbackSuccess, callbackError, callbackFinally)
    },
    /**
     * Отправка данных на сервер POST
     * @param {Object} params
     * @param {function} [callbackSuccess=null]
     * @param {function} [callbackError=null]
     * @param {function} [callbackFinally=null]
     */
    post: function (params, callbackSuccess, callbackError, callbackFinally) {
      return this.ajax('post', params, callbackSuccess, callbackError, callbackFinally)
    },

    //---------------/**
    //--------------- * Отправка файлы на сервер
    //--------------- * @param {Object} formData
    //--------------- * @param {function} callbackSuccess
    //--------------- * @param {function} callbackError
    //--------------- * @param {function} callbackFinally
    //--------------- */
    //---------------sendFile: function (formData, callbackSuccess, callbackError, callbackFinally) {
    //---------------  return this.ajax('send-file', formData, callbackSuccess, callbackError, callbackFinally)
    //---------------},

    /**
     * Отправка данных на сервер
     * @param {string} method
     * @param {Object} params
     * @param {function} callbackSuccess
     * @param {function} callbackError
     * @param {function} callbackFinally
     */
    ajax: function (method, params, callbackSuccess, callbackError, callbackFinally) {
      //---------------const isSendFile = method === 'send-file'

      if (params.data === undefined) {
        params.data = {}
      }

      //---------------if (!params.url.startsWith(this.base)) {
      //---------------  params.url = this.base + params.url
      //---------------}
      //---------------
      //---------------if (isSendFile) {
      //---------------  method = 'post'
      //---------------  params.data.append('sid', store.state.sid)
      //---------------} else {
      //---------------  params.data.sid = store.state.sid
      //---------------}
      //---------------
      //---------------const data = isSendFile ? params.data : { params: params.data }
      const data = { params: params.data }

      Axios[method](
        params.url,
        data,
        {
          headers: {
            'Content-Type': 'application/json;charset=UTF-8',
            'SD-SID': store.state.sid
          }
        }
      )
        .then(function (response) {
          const data = response.data || {}

          //---------------const pageTitle = data.title || null
          //---------------if (pageTitle !== null || data.title === '') {
          //---------------  store.commit('pageTitle_set', pageTitle)
          //---------------}

          if (response.status == 200) {
            if (typeof callbackSuccess === 'function') {
              return callbackSuccess(data)
            }
          }
        })
        .catch(function (error) {
          if (error.response !== undefined) {
            const data = error.response.data || {}

            if (error.response.status == 400) {
              if (typeof callbackError === 'function') {
                return callbackError(data)
              }
            }

            if (error.response.status == 401) {
              if ('message' in data) {
                Message({
                  message: data.message,
                  showClose: true,
                  type: 'error'
                })

                return false
              }
            }

            if (error.response.status == 403) {
              if (typeof callbackError === 'function') {
                return callbackError(data)
              }
            }

            if (error.response.status == 404) {
              if (typeof callbackError === 'function') {
                return callbackError(data)
              }
            }

            if ('message' in data) {
              Message({
                message: data.message,
                showClose: true,
                type: 'error'
              })

              return false
            }

            //---------------console.log(error.response)
          }
        })
        .finally(function () {
          if (typeof callbackFinally === 'function') {
            return callbackFinally()
          }
        })

      return true
    }
  },









  /**
   * Дата сегодня
   * @returns {string}
   */
  today: function () {
    const today = new Date()
    const dd = String(today.getDate()).padStart(2, '0')
    const mm = String(today.getMonth() + 1).padStart(2, '0')
    const yyyy = today.getFullYear()

    return dd + '.' + mm + '.' + yyyy
  },
  /**
   * Текущее время
   * @returns {string}
   */
  currentTime: function () {
    const today = new Date()
    let h = today.getHours(); if (h < 10) h = '0' + h
    let m = today.getMinutes(); if (m < 10) m = '0' + m

    return h + ':' + m
  },
  /**
   * Дата сегодня со временем
   * @returns {string}
   */
  todayWithTime: function () {
    return this.today() + ' ' + this.currentTime()
  },
  /**
   * Дата сегодня со смещением месяца
   * @param {number} delta смещение в месяцах
   * @param {boolean} [isFirstDay=false] установить первый день месяца
   * @returns {string} дата
   */
  todayAddMonth (delta, isFirstDay = false) {
    let today = new Date()
    const todayDiff = today.setMonth(today.getMonth() + delta)
    today = new Date(todayDiff)

    const dd = isFirstDay ? '01' : String(today.getDate()).padStart(2, '0')
    const mm = String(today.getMonth() + 1).padStart(2, '0')
    const yyyy = today.getFullYear()

    return dd + '.' + mm + '.' + yyyy
  },
  /**
   * Вычитание дней из даты
   * @param {string} date дата в формате "dd.mm.yyyy"
   * @param {number} interval кол-во дней
   */
  date_add_days (date, interval) {
    const dateArray = date.split('.')
    if (dateArray.length !== 3) return date
    const d = dateArray[0]
    const m = dateArray[1]
    const y = dateArray[2]

    const dt = new Date(y, m - 1, d)
    dt.setDate(dt.getDate() + interval)

    const dd = String(dt.getDate()).padStart(2, '0')
    const mm = String(dt.getMonth() + 1).padStart(2, '0')
    const yyyy = dt.getFullYear()

    return dd + '.' + mm + '.' + yyyy
  },
  /**
   * клонировать объект
   * @param {any} object
   * @returns {any}
   */
  clone: function (object) {
    if (object == null) return object

    let a = null
    if (Object.prototype.toString.call(object) === '[object Array]') {
      a = []
    } else {
      a = {}
    }

    for (const key in object) {
      if (typeof object[key] === 'object') {
        a[key] = this.clone(object[key])
      } else {
        a[key] = object[key]
      }
    }

    return a
  },
  /**
   * скачивание файла с сервера
   * @param {string} path url-путь к файлы
   * @param {string} name имя файла для сохранения
   * @returns {void}
   */
  downloadFile (path, name) {
    const ext = path.split('.').pop()

    const link = document.createElement('a')
    link.setAttribute('href', path)
    link.setAttribute('download', name + '.' + ext)
    link.click()
  },
  /**
   * Случайное число в диапозоне
   * @param {number} [min=1000]
   * @param {number} [max=1000000]
   * @returns {number}
   */
  getRandom (min, max) {
    if (typeof min === 'undefined') min = 1000
    if (typeof max === 'undefined') max = 1000000

    return Math.floor(Math.random() * (max - min)) + min
  },
  /**
   * подстановка знака +/- перед числом
   * @param {number} value
   * @returns {string}
   */
  get_time_offset_sign (value) {
    if (!value) return ''
    return (value > 0 ? '+' : '') + value
  },
  /**
   * Открыть новое окно и передать GET запрос
   * @param {string} url адрес
   * @param {number} [height=200] высота
   * @param {number} [width=200] ширина
   * @param {function} [callback=null] функция после закрытия окна
   * @returns {Window}
   */
  newWindowOpen (url, height = 200, width = 200, callback = null) {
    const screen = window.screen
    const left = Math.floor(0.5 * (screen.width - width))
    const top = Math.floor(0.5 * (screen.height - height))
    height -= 150

    const params = 'left=' + left + ', top=' + top + ', height=' + height + ', width= ' + width + ', menubar=no, toolbar=no, location=no, directories=no, status=no, resizable=yes, scrollbars=yes'
    const popapW = window.open(url, '', params)
    if (typeof callback === 'function') {
      popapW.addEventListener('beforeunload', callback)
    }

    return popapW
  },
  /**
   * Открыть новое окно и передать POST запрос
   * @param {string} url адрес
   * @param {Object} data данные {name: value}
   * @param {number} [height=200] высота
   * @param {number} [width=200] ширина
   * @param {function} [callback=null] функция после закрытия окна
   * @returns {Window}
   */
  newWindowOpenWithPostData (url, data, height = 200, width = 200, callback) {
    const screen = window.screen
    const left = Math.floor(0.5 * (screen.width - width))
    const top = Math.floor(0.5 * (screen.height - height))
    height -= 150

    const params = 'left=' + left + ', top=' + top + ', height=' + height + ', width= ' + width + ', menubar=no, toolbar=no, location=no, directories=no, status=no, resizable=yes, scrollbars=yes'
    const popapW = window.open(url, '', params)
    if (typeof callback === 'function') {
      popapW.addEventListener('beforeunload', callback)
    }

    let htmlText = `<html><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        </head><body><form method="POST" action="${url}" id="form">`

    for (const name in data) {
      htmlText += `<input type="hidden" name="${name}" value="${data[name]}">`
    }

    htmlText += '</form></body></html>'

    popapW.document.write(htmlText)
    popapW.document.close()
    popapW.document.getElementById('form').submit()

    return popapW
  },
  /**
   * Открыть новую вкладку
   * @param {string} url адрес
   * @returns {void}
   */
  newTabOpen (url) {
    window.open(url, '_blank')
  },
  /**
   * Создать массив указанной длины со значениями
   * @param {number} len длина массива
   * @param {*} value значение элемента массива
   * @returns {Array<*>} массив
   */
  array_fill (len, value) {
    const a = []
    for (let i = 0; i < len; i++) {
      a.push(value)
    }
    return a
  },
  /**
   * Проверка наличия элемента в массиве
   * @param {number|string} value значение
   * @param {Array<number|string>} arr массив
   * @returns {boolean}
   */
  inArray (value, arr) {
    for (let i = 0; i < arr.length; i++) {
      if (arr[i] == value) {
        return true
      }
    }
    return false
  },
  /**
   * Проверка что переменная - массив
   * @param {*} value переменная
   * @returns {boolean}
   */
  isArray (value) {
    return Object.prototype.toString.call(value) === '[object Array]'
  },
  /**
   * Проверка что переменная - объект
   * @param {*} value переменная
   * @returns {boolean}
   */
  isObject (value) {
    return Object.prototype.toString.call(value) === '[object Object]'
  },
  /**
   * получить значение из многомерного массива по пути
   * @param {Object|Array} obj переменная
   * @param {string} path путь ("orders.details.status", "0.2.4.8")
   * @returns {*|undefined}
   */
  arrayGetItem (obj, path) {
    return path.split('.').reduce(function (acc, c) { return acc && acc[c] }, obj)
  },
  /**
   * задержка
   * @param {number} ms задержка в мс
   * @returns {void}
   */
  sleep (ms) {
    const date = Date.now()
    let currentDate = null
    do {
      currentDate = Date.now()
    } while (currentDate - date < ms)
  },
  /**
   * Записать значение в localStorage
   */
  storageSetItem (key, value) {
    const localStorage = window.localStorage
    if (!localStorage) return false
    localStorage.setItem(key, JSON.stringify(value))
  },
  /**
   * Получить значение из localStorage
   */
  storageGetItem (key, defaultValue = null) {
    const localStorage = window.localStorage
    if (!localStorage) return defaultValue
    const value = localStorage.getItem(key)
    if (value === null) return defaultValue
    return JSON.parse(value)
  }

}

/**
 * снятие фокуса со всех элемнтов
 * @returns {void}
 */
export function clearBlur () { document.activeElement.blur() }
