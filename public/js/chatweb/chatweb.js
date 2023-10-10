$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

const wsUrl = `ws://${chatwsUrl}`;
var socket = new WebSocket(wsUrl);
setTimeout(function() { 
    var connectRequest = {
        msg: "connect",
        version: "1",
        support: ["1", "pre2", "pre1"]
    }
    socket.send(JSON.stringify(connectRequest));
}, 250);

var myStorage = localStorage;
var userToken = myStorage.getItem('guestID');
if (!userToken) {
    userToken = createUUID();
    myStorage.setItem('guestID', userToken);
}

var element = $('.floating-chat');
// var userToken = createUUID();
var uniqueCode = userToken.substring(0, 3);
var roomChatId = null;
var createRoomstate = false;
var closeRoomstate = false;


$(document).ready(function() {
    console.log(userToken);
    socketHandler(socket);
    element.find('#sendMessage').click(function() {
        sendNewMessage();
    });
});

function socketHandler(socket) {
    // Event handler for receiving messages from the server
    socket.onmessage = (event) => {
        const message = jQuery.parseJSON(event.data);

        // Keep connection alive
        if (message.msg == 'ping') {
            keepAliveConn();
        }

        // Get new message
        if (message.fields != undefined) {
            console.log(message);
            if (message.fields.args != undefined && message.fields.args[0].t == undefined) {
                getNewMessage(message);
            } else if (message.fields.args != undefined && message.fields.args[0].t == 'livechat-close') {
                closeRoomstate = true;
                $('.chat_title').html('Virtual Assistant');
                $('.messages').append([
                    `<li class="notif">Conversation Off</li>`
                ].join(''));

                $('.messages').finish().animate({
                    scrollTop: $('.messages').prop("scrollHeight")
                }, 250);
            }
        }
    };

    // Event handler for when an error occurs
    socket.onerror = (event) => {
        console.error("WebSocket error:", event);
    };

    // Event handler for when the connection is closed
    socket.onclose = (event) => {
        reconnectSocket();
    };
}

function keepAliveConn() {
    var connectRequest = {
        msg: "pong",
    }
    socket.send(JSON.stringify(connectRequest));
}

function getNewMessage(message) {
    var messagesContainer = $('.messages');
    var sender = (message.fields.args[0].token != undefined) ? 'self' : 'other';
    var txtMsg = message.fields.args[0].msg.replace(/\n/g, '<br>');

    // Append message to chat box
    if (message.fields.args[0].attachments != undefined 
        && message.fields.args[0].attachments[0].actions != undefined) {
        var actions = message.fields.args[0].attachments[0].actions;

        $.each( actions, function( key, value ) {
            if (value.type == 'button') {
                messagesContainer.append([
                    `<a href='#' 
                        class='bot-button' 
                        onclick="sendCommand('${value.text}')"
                    >
                    <li class="bot">`,
                    value.text,
                    `</li>
                    </a>`
                ].join(''));
            }
        });
    } else {
        messagesContainer.append([
            `<li class="${sender}">`,
            txtMsg,
            '</li>'
        ].join(''));
    }

    messagesContainer.finish().animate({
        scrollTop: messagesContainer.prop("scrollHeight")
    }, 250);

    if (sender == 'other') {
        $("#notif_sound").get(0).play();
    }

    // If event guest message
    if (message.fields.args[0].token) {
        var userInput = $('.text-box');

        // clean out old message
        userInput.html('');
        // focus on input
        userInput.focus();
    }
}

function reconnectSocket() {
    socket = new WebSocket(wsUrl);
    // recall socket handler
    socketHandler(socket);
    
    setTimeout(function() { 
        var connectRequest = {
            msg: "connect",
            version: "1",
            support: ["1", "pre2", "pre1"]
        }
        socket.send(JSON.stringify(connectRequest));
        getChatHistory(socket);
    }, 250);
}

function createChatRoom() {
    $.ajax({
        url: `/dashboard/create-chat-room?token=${userToken}`,
        type: 'GET',
        success: function (data) {
            if (data.room_id != undefined) {
                roomChatId = data.room_id;
                $('.chat_title').html('Agent: ' + data.agent);
                console.log(roomChatId);
                getChatHistory(socket);
            }
        },
        error: function (xhr, status, error) {
            var err = jQuery.parseJSON(xhr.responseJSON.message);
            console.log(err);

            $('.messages').append([
                `<li class="notif">${err.error.replace(/\[[^\]]*\]/g, '')}</li>`
            ].join(''));
        },
    });
}

function getChatHistory(socket) {
    var streamChat = {
        msg: "sub",
        id: roomChatId,
        name: "stream-room-messages",
        params: [
            roomChatId,
            {
                useCollection:false,
                args:[{ visitorToken: userToken }]
            }
        ]
    }
    socket.send(JSON.stringify(streamChat));
}

setTimeout(function() {
    element.addClass('enter');
}, 1000);

element.click(openElement);

$('.main-body').on('click', function() {
    closeElement();
});

function openElement() {
    var messages = element.find('.messages');
    var textInput = element.find('.text-box');
    element.find('>i').hide();
    element.addClass('expand');
    element.find('.chat').addClass('enter');
    var strLength = textInput.val().length * 2;
    textInput.keydown(onMetaAndEnter).prop("disabled", false).focus();
    element.off('click', openElement);
    element.find('.header button').click(closeElement);
    messages.scrollTop(messages.prop("scrollHeight"));

    if (createRoomstate == false) {
        var data = {
            token: userToken,
            name: `Dummy-${uniqueCode}`,
            // email: `dummy.${uniqueCode}@sampledummy.com`
        };
    
        // Create guest live chat contact
        $.ajax({
            url: '/dashboard/create-chat-contact',
            type: 'POST',
            dataType: "json",
            data: data,
            success: function (data) {
                createChatRoom();
                createRoomstate = true;
            },
            error: function (xhr, status, error) {
                console.log(xhr.responseText);
            },
        });
    }
}

function closeElement() {
    element.find('.chat').removeClass('enter').hide();
    element.find('>i').show();
    element.removeClass('expand');
    element.find('.header button').off('click', closeElement);
    element.find('.text-box').off('keydown', onMetaAndEnter).prop("disabled", true).blur();
    setTimeout(function() {
        element.find('.chat').removeClass('enter').show()
        element.click(openElement);
    }, 500);
}

function createUUID() {
    // http://www.ietf.org/rfc/rfc4122.txt
    var s = [];
    var hexDigits = "0123456789abcdef";
    for (var i = 0; i < 36; i++) {
        s[i] = hexDigits.substr(Math.floor(Math.random() * 0x10), 1);
    }
    s[14] = "4"; // bits 12-15 of the time_hi_and_version field to 0010
    s[19] = hexDigits.substr((s[19] & 0x3) | 0x8, 1); // bits 6-7 of the clock_seq_hi_and_reserved to 01
    s[8] = s[13] = s[18] = s[23] = "-";

    var uuid = s.join("");
    return uuid;
}

function createRandString(length = 16) {
    var s = [];
    var hexDigits = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
    for (var i = 0; i < length; i++) {
        s[i] = hexDigits.substr(Math.floor(Math.random() * 0x10), 1);
    }

    var randstring = s.join("");
    return randstring;
}

function sendNewMessage(input = null) {
    var userInput = $('.text-box');
    var newMessage = userInput.html().replace(/\<div\>|\<br.*?\>/ig, '\n').replace(/\<\/div\>/g, '').trim();
    var randId = createRandString();
    var messagesContainer = $('.messages');

    if (input) {
        newMessage = input;
    }
    
    if (!newMessage) return;

    if (newMessage.toLowerCase() == 'mulai' && closeRoomstate == true) {
        createChatRoom();
        closeRoomstate = false;

        messagesContainer.append([
            '<li class="self">',
            newMessage,
            '</li>'
        ].join(''));
        messagesContainer.finish().animate({
            scrollTop: messagesContainer.prop("scrollHeight")
        }, 250);
        setTimeout(function() { 
            userInput.html('');
            userInput.focus();
        }, 50);
        
        
        return;
    }

    if (closeRoomstate == true) {
        messagesContainer.append([
            '<li class="other">',
            'Sesi chat sudah ditutup, silahkan ketik "mulai" untuk memulai kembali',
            '</li>'
        ].join(''));
        messagesContainer.finish().animate({
            scrollTop: messagesContainer.prop("scrollHeight")
        }, 250);
        setTimeout(function() { 
            userInput.html('');
            userInput.focus();
        }, 50);

        return;
    }

    // Send message via websocket
    var sendMsg = {
        msg: "method",
        method: "sendMessageLivechat",
        params: [
            {
                _id: randId,
                rid: roomChatId,
                msg: newMessage,
                token: userToken
            }
        ],
        id: randId
    };
    socket.send(JSON.stringify(sendMsg));

    if (newMessage.toLowerCase() == 'selesai') {
        closeChatRoom();
    }
}

function closeChatRoom() {
    $.ajax({
        url: '/dashboard/close-room',
        type: 'POST',
        dataType: "json",
        data: {
            token: userToken,
            rid: roomChatId
        },
        success: function (data) {
            if (data.success) {
                closeRoomstate = true;
                $('.chat_title').html('Virtual Assistant');
            }
        },
        error: function (xhr, status, error) {
            console.log(xhr.responseText);
        },
    });
}

function sendCommand(msg) {
    sendNewMessage(msg);
    $('.bot-button').remove();
}

function onMetaAndEnter(event) {
    if ((event.metaKey || event.shiftKey) && event.keyCode == 13) {
        var textInput = $('.text-box');
        textInput.append('<br/>');
        textInput.focus();
    } else if (event.keyCode == 13) {
        sendNewMessage();
    }
}