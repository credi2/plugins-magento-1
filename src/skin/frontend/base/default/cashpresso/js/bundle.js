var C2EcomWizard = function () {
    "use strict";
    var Environment = {};

    function initialize() {
        var script = document.getElementById("c2LabelScript");
        Environment.mode = script.getAttribute("data-c2-mode");
        Environment.apiKey = script.getAttribute("data-c2-partnerApiKey");
        Environment.interestFreeDaysMerchant = script.getAttribute("data-c2-interestFreeDaysMerchant") || 0;
        Environment.checkoutCallback = script.getAttribute("data-c2-checkoutCallback") && script.getAttribute("data-c2-checkoutCallback") === "true";
        Environment.c2PrefillData = {
            email: script.getAttribute("data-c2-email"),
            given: script.getAttribute("data-c2-given"),
            family: script.getAttribute("data-c2-family"),
            birthdate: script.getAttribute("data-c2-birthdate"),
            country: script.getAttribute("data-c2-country"),
            city: script.getAttribute("data-c2-city"),
            zip: script.getAttribute("data-c2-zip"),
            addressline: script.getAttribute("data-c2-addressline"),
            phone: script.getAttribute("data-c2-phone"),
            iban: script.getAttribute("data-c2-iban"),
            checkoutCallback: Environment.checkoutCallback
        };
        Environment.sessionCookieName = "c2EcomId";
        Environment.customerStateKey = "customerState";
        Environment.customerStateRequest = "getCustomerState";
        Environment.responseErrorCount = 0;
        Environment.selectorAmountMap = new Map;
        if (Environment.mode === "test") {
            Environment.baseUrl = "https://test.cashpresso.com/frontend/ecommerce/v2/label/";
            Environment.wizardUrl = "https://test.cashpresso.com/frontend/ecommerce/v2/overlay-wizard/index.html#/init/" + Environment.mode + "/" + Environment.apiKey;
            Environment.backendBaseUrl = "https://test.cashpresso.com/rest/backend/ecommerce/v2"
        } else if (Environment.mode === "local") {
            Environment.baseUrl = "http://localhost:8082/frontend/ecommerce/v2/label/";
            Environment.wizardUrl = "http://localhost:8082/frontend/ecommerce/v2/overlay-wizard/index.html#/init/" + Environment.mode + "/" + Environment.apiKey;
            Environment.backendBaseUrl = "http://localhost:8080/rest/backend/ecommerce/v2"
        } else {
            Environment.baseUrl = "https://my.cashpresso.com/ecommerce/v2/label/";
            Environment.wizardUrl = "https://my.cashpresso.com/ecommerce/v2/overlay-wizard/index.html#/init/" + Environment.mode + "/" + Environment.apiKey;
            Environment.backendBaseUrl = "https://backend.cashpresso.com/rest/backend/ecommerce/v2"
        }
        Environment.locale = script.getAttribute("data-c2-locale") || document.documentElement.lang || navigator.language || navigator.userLanguage || "de";
        if (Environment.locale === "de") {
            Environment.thousandSeparator = ".";
            Environment.decimalSeparator = ","
        } else {
            Environment.thousandSeparator = ",";
            Environment.decimalSeparator = "."
        }
        initEcomIdAndCustomerState();
        loadCSS();
        var labels = document.getElementsByClassName("c2-financing-label");
        var i = 0;
        var id = null;
        var amount = null;
        for (i = 0; i < labels.length; i += 1) {
            id = labels[i].getAttribute("id");
            if (!id || id === "") {
                id = "c2-financing-label-" + i;
                labels[i].setAttribute("id", id)
            }
            amount = parseFloat(labels[i].getAttribute("data-c2-financing-amount"));
            if (amount <= 0) {
                console.error("provided amount of " + amount + " is invalid")
            }
            initPurchaseLabel(amount, id)
        }
    }

    function initEcomIdAndCustomerState() {
        Environment.ecomSessionId = getSessionCookie();
        if (Environment.ecomSessionId === "" || !Environment.ecomSessionId) {
            httpPostAsync(Environment.customerStateRequest, getCustomerStateRequest(), setAndStoreResponseInfo);
            return
        }
        var response = JSON.parse(getFromLocalStorage(Environment.customerStateKey));
        if (response && response.creationDate) {
            var ageInMin = (new Date - new Date(response.creationDate)) / 6e4;
            if (ageInMin < 1) {
                setCustomerState(response);
                return
            }
        }
        httpPostAsync(Environment.customerStateRequest, getCustomerStateRequest(), setAndStoreResponseInfo)
    }

    function initPurchaseLabel(purchaseAmount, targetSelector) {
        Environment.selectorAmountMap.set(targetSelector, purchaseAmount);
        var url = Environment.baseUrl + "c2_ecom_label.html";
        httpRequestAsync(url, "GET", null, function (text) {
            document.getElementById(targetSelector).innerHTML = text;
            initLabel(purchaseAmount, targetSelector)
        })
    }

    function initLabelFromSelector(targetSelector) {
        var purchaseAmount = Environment.selectorAmountMap.get(targetSelector);
        initLabel(purchaseAmount, targetSelector)
    }

    function initLabel(purchaseAmount, targetSelector) {
        var amount = calculateAmount(purchaseAmount);
        if (document.getElementById(targetSelector).getElementsByClassName("c2-amount-label")[0]) {
            document.getElementById(targetSelector).getElementsByClassName("c2-amount-label")[0].innerHTML = getLocalizedLabel(amount);
            if (amountExceedsLimit(purchaseAmount)) {
                document.getElementById(targetSelector).getElementsByClassName("c2-ecom-label")[0].classList.add("c2-exceeded");
                document.getElementById(targetSelector).getElementsByClassName("c2-ecom-label")[0].classList.remove("c2-checked");
                document.getElementById(targetSelector).getElementsByClassName("c2-mark")[0].classList.remove("c2-checkmark-side");
                document.getElementById(targetSelector).getElementsByClassName("c2-mark")[0].classList.remove("c2-icon")
            } else if (Environment.customerState === "ACTIVE" || Environment.customerState === "OK") {
                document.getElementById(targetSelector).getElementsByClassName("c2-ecom-label")[0].classList.add("c2-checked");
                document.getElementById(targetSelector).getElementsByClassName("c2-ecom-label")[0].classList.remove("c2-exceeded");
                document.getElementById(targetSelector).getElementsByClassName("c2-mark")[0].classList.add("c2-checkmark-side");
                document.getElementById(targetSelector).getElementsByClassName("c2-mark")[0].classList.add("c2-icon")
            } else {
                document.getElementById(targetSelector).getElementsByClassName("c2-ecom-label")[0].classList.remove("c2-checked");
                document.getElementById(targetSelector).getElementsByClassName("c2-ecom-label")[0].classList.remove("c2-exceeded");
                document.getElementById(targetSelector).getElementsByClassName("c2-mark")[0].classList.remove("c2-checkmark-side")
            }
        }
        if (document.getElementById(targetSelector).getElementsByTagName("a")[0]) {
            document.getElementById(targetSelector).getElementsByTagName("a")[0].addEventListener("click", function (event) {
                event.data = {selector: targetSelector};
                C2EcomWizard.startOverlayWizard(event)
            })
        }
    }

    function getLocalizedLabel(amount) {
        var formatted = amount._formatMoney(2, Environment.decimalSeparator, Environment.thousandSeparator);
        if (Environment.locale === "en") {
            return "from â‚¬ " + formatted + " / month"
        }
        return "ab " + formatted + " â‚¬ / Monat"
    }

    function setAndStoreResponseInfo(text) {
        var response = JSON.parse(text);
        if (hasError(response)) {
            return
        }
        response.creationDate = new Date;
        setInLocalStorage(Environment.customerStateKey, JSON.stringify(response));
        setCustomerState(response)
    }

    function hasError(response) {
        if (response.success) {
            Environment.responseErrorCount = 0;
            return false
        }
        if (response.error && response.error.type === "INVALID_INPUT" && response.error.description.indexOf("c2EcomId") >= 0 && Environment.responseErrorCount < 2) {
            Environment.ecomSessionId = null;
            deleteCookie(Environment.sessionCookieName);
            httpPostAsync(Environment.customerStateRequest, getCustomerStateRequest(), setAndStoreResponseInfo)
        }
        Environment.responseErrorCount++;
        return true
    }

    function setCustomerState(customerState) {
        Environment.customerState = customerState.state;
        Environment.ecomSessionId = customerState.c2EcomId;
        Environment.minimumAmount = customerState.minInstalment.min;
        Environment.paybackRate = customerState.minInstalment.factor / 100;
        if (Environment.customerState && Environment.customerState === "ACTIVE" && customerState.payment) {
            Environment.nextInstalment = customerState.payment.next;
            Environment.openAmount = customerState.payment.open;
            Environment.availableAmount = customerState.payment.available;
            Environment.maxAvailable = customerState.payment.maxAvailable
        } else {
            Environment.nextInstalment = 0;
            Environment.openAmount = 0;
            Environment.availableAmount = 0;
            Environment.maxAvailable = 0
        }
        createSessionCookie(Environment.sessionCookieName, Environment.ecomSessionId, 30);
        Environment.selectorAmountMap.forEach(function (item, key, mapObj) {
            initLabelFromSelector(key)
        })
    }

    function getCustomerStateRequest() {
        return {partnerApiKey: Environment.apiKey, c2EcomId: Environment.ecomSessionId}
    }

    function calculateAmount(purchaseAmount) {
        if (amountExceedsLimit(purchaseAmount)) {
            return calculateMinAmount(purchaseAmount)
        }
        if (purchaseAmount && Environment.customerState === "ACTIVE") {
            return calculateAmountExistingCustomer(purchaseAmount)
        }
        if (purchaseAmount) {
            return calculateAmountNewCustomer(purchaseAmount)
        }
    }

    function amountExceedsLimit(purchaseAmount) {
        return purchaseAmount > Environment.maxAvailable && Environment.customerState === "ACTIVE"
    }

    function calculateAmountNewCustomer(purchaseAmount) {
        return Math.min(purchaseAmount, Math.max(Environment.minimumAmount, purchaseAmount * Environment.paybackRate))
    }

    function calculateAmountExistingCustomer(purchaseAmount) {
        var financiableAmount = Math.min(Environment.availableAmount, purchaseAmount);
        var openAmountNew = Environment.openAmount + financiableAmount;
        var nextInstalmentNew = Math.min(openAmountNew, Math.max(Environment.minimumAmount, openAmountNew * Environment.paybackRate));
        return Math.max(0, nextInstalmentNew - Environment.nextInstalment)
    }

    function calculateMinAmount(purchaseAmount) {
        var paybackRateAmount = purchaseAmount * Environment.paybackRate;
        return Math.min(purchaseAmount, Math.max(Environment.minimumAmount, paybackRateAmount))
    }

    function handleMessage(event) {
        var data = event.data;
        if (data.function === "c2UpdateEcomId") {
            createSessionCookie(Environment.sessionCookieName, data.c2EcomId, 30)
        }
        if (data.function === "c2RequestPrefillData") {
            sendOptionalDataAttributes()
        }
        if (data.function === "c2CloseWizard") {
            c2CloseWizard()
        }
        if (data.purchased === true && Environment.checkoutCallback && window.c2Checkout) {
            window.c2Checkout()
        }
    }

    function startWizard(event) {
        if (!Environment.ecomSessionId) {
            Environment.ecomSessionId = getSessionCookie()
        }
        var frame = document.getElementById("c2WizardFrame");
        var purchaseAmount = Environment.selectorAmountMap.get(event.data.selector);
        if (!frame) {
            var newFrame = document.createElement("iframe");
            newFrame.id = "c2WizardFrame";
            newFrame.height = "auto";
            newFrame.width = "100%";
            newFrame.style.position = "fixed";
            newFrame.style.top = "0px";
            newFrame.style.left = "0px";
            newFrame.style.right = "0px";
            newFrame.style.bottom = "0px";
            newFrame.style.minHeight = "100%";
            newFrame.style.height = "100%";
            newFrame.style.zIndex = "9999999999";
            newFrame.src = Environment.wizardUrl + "/" + Environment.ecomSessionId + "/" + purchaseAmount + "/" + Environment.interestFreeDaysMerchant + "/" + false;
            newFrame.allowtransparency = "true";
            document.body.appendChild(newFrame)
        } else {
            frame.contentWindow.postMessage({
                function: "c2UpdateSession",
                c2PurchaseAmount: purchaseAmount,
                c2EcomId: Environment.ecomSessionId
            }, "*");
            frame.style.display = "block"
        }
        disableScroll()
    }

    function c2CloseWizard() {
        removeFromLocalStorage(Environment.customerStateKey);
        initEcomIdAndCustomerState();
        enableScroll();
        document.getElementById("c2WizardFrame").style.display = "none"
    }

    function refreshAmountForLabel(labelId, newAmount) {
        if (!labelId || !Environment.selectorAmountMap.get(labelId)) {
            return
        }
        if (newAmount <= 0) {
            console.error("provided amount of " + newAmount + " is invalid");
            return
        }
        Environment.selectorAmountMap.set(labelId, newAmount);
        initLabel(newAmount, labelId)
    }

    function disableScroll() {
        var body = document.body;
        var html = document.documentElement;
        if (!html.classList.contains("c2-noscroll") && body.scrollHeight > html.clientHeight) {
            var scrollTop = html.scrollTop ? html.scrollTop : body.scrollTop;
            html.classList.add("c2-noscroll");
            html.style.top = "" + -scrollTop + "px"
        }
    }

    function enableScroll() {
        var body = document.body;
        var html = document.documentElement;
        if (html.classList.contains("c2-noscroll")) {
            var scrollTop = parseInt(html.style.top);
            html.classList.remove("c2-noscroll");
            html.style.top = null;
            html.scrollTop = -scrollTop
        }
    }

    function refreshOptionalDataAttributes(options) {
        if (!Environment.c2PrefillData) {
            console.log("please do not call refreshOptionalData before init has run (after DOM has loaded)");
            return
        }
        Environment.c2PrefillData.email = options.email ? options.email : Environment.c2PrefillData.email;
        Environment.c2PrefillData.given = options.given ? options.given : Environment.c2PrefillData.given;
        Environment.c2PrefillData.family = options.family ? options.family : Environment.c2PrefillData.family;
        Environment.c2PrefillData.birthdate = options.birthdate ? options.birthdate : Environment.c2PrefillData.birthdate;
        Environment.c2PrefillData.country = options.country ? options.country : Environment.c2PrefillData.country;
        Environment.c2PrefillData.city = options.city ? options.city : Environment.c2PrefillData.city;
        Environment.c2PrefillData.zip = options.zip ? options.zip : Environment.c2PrefillData.zip;
        Environment.c2PrefillData.addressline = options.addressline ? options.addressline : Environment.c2PrefillData.addressline;
        Environment.c2PrefillData.phone = options.phone ? options.phone : Environment.c2PrefillData.phone;
        Environment.c2PrefillData.iban = options.iban ? options.iban : Environment.c2PrefillData.iban;
        Environment.c2PrefillData.checkoutCallback = Environment.checkoutCallback;
        sendOptionalDataAttributes()
    }

    function sendOptionalDataAttributes() {
        var frame = document.getElementById("c2WizardFrame");
        if (frame) {
            frame.contentWindow.postMessage({
                function: "c2SetPrefillData",
                c2PrefillData: Environment.c2PrefillData
            }, "*")
        }
    }

    function httpPostAsync(endpoint, data, callback) {
        var url = Environment.backendBaseUrl + "/" + endpoint;
        httpRequestAsync(url, "POST", data, callback)
    }

    function httpRequestAsync(url, method, data, callback) {
        var xhr = new XMLHttpRequest;
        xhr.open(method, url, true);
        xhr.setRequestHeader("Content-Type", "application/json");
        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4 && xhr.status === 200) callback(xhr.responseText)
        };
        data = data ? JSON.stringify(data) : null;
        xhr.send(data)
    }

    function createSessionCookie(name, value, expirationDays) {
        var d = new Date;
        d.setTime(d.getTime() + expirationDays * 24 * 60 * 60 * 1e3);
        var expires = "expires=" + d.toUTCString();
        document.cookie = name + "=" + value + ";" + expires + ";path=/"
    }

    function getSessionCookie() {
        var name = Environment.sessionCookieName + "=";
        var decodedCookie = decodeURIComponent(document.cookie);
        var ca = decodedCookie.split(";");
        var i = 0;
        var c = null;
        for (i = 0; i < ca.length; i += 1) {
            c = ca[i];
            while (c.charAt(0) === " ") {
                c = c.substring(1)
            }
            if (c.indexOf(name) === 0) {
                return c.substring(name.length, c.length)
            }
        }
        return ""
    }

    function deleteCookie(name) {
        createSessionCookie(name, "", -1)
    }

    function loadCSS() {
        if (document.getElementById("c2LabelStylesheet")) {
            return
        }
        var link = document.createElement("link");
        link.id = "c2LabelStylesheet";
        link.href = Environment.baseUrl + "c2_ecom_styles.css";
        link.type = "text/css";
        link.rel = "stylesheet";
        link.media = "screen,print";
        document.getElementsByTagName("head")[0].appendChild(link)
    }

    function isProductionMode() {
        return Environment.mode !== "local" && Environment.mode !== "test"
    }

    function setInLocalStorage(key, value) {
        if (checkLocalStorage()) {
            window.localStorage.setItem(key, value)
        }
    }

    function getFromLocalStorage(key) {
        if (checkLocalStorage()) {
            return window.localStorage.getItem(key)
        }
        return null
    }

    function removeFromLocalStorage(key) {
        if (checkLocalStorage()) {
            window.localStorage.removeItem(key)
        }
    }

    function checkLocalStorage() {
        try {
            window.localStorage.setItem("c2EcomLocalStorageCheck", 1);
            window.localStorage.removeItem("c2EcomLocalStorageCheck");
            return true
        } catch (error) {
            console.error("window.localStorage is not available - either browser does not support it or it is blocked\n" + error);
            return false
        }
    }

    Number.prototype._formatMoney = function (c, d, t) {
        var n = this;
        var s = n < 0 ? "-" : "";
        var i = String(parseInt(n = Math.abs(Number(n) || 0).toFixed(c)));
        var j = (j = i.length) > 3 ? j % 3 : 0;
        c = isNaN(c = Math.abs(c)) ? 2 : c;
        d = d === undefined ? "." : d;
        t = t === undefined ? "," : t;
        return s + (j ? i.substr(0, j) + t : "") + i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + t) + (c ? d + Math.abs(n - i).toFixed(c).slice(2) : "")
    };
    return {
        init: function () {
            initialize()
        }, onMessage: function (event) {
            handleMessage(event)
        }, startOverlayWizard: function (event) {
            startWizard(event)
        }, refreshOptionalData: function (optional) {
            refreshOptionalDataAttributes(optional)
        }, refreshAmount: function (labelId, newAmount) {
            refreshAmountForLabel(labelId, newAmount)
        }
    }
}();
if (window.addEventListener) {
    window.addEventListener("message", C2EcomWizard.onMessage, false)
} else if (window.attachEvent) {
    window.attachEvent("onmessage", C2EcomWizard.onMessage, false)
}
document.addEventListener("DOMContentLoaded", function (event) {
    "use strict";
    C2EcomWizard.init()
});
