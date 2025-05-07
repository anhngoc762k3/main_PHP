<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (isset($_POST['question'])) {
    $question = htmlspecialchars($_POST['question'], ENT_QUOTES, 'UTF-8');

    $pythonScriptPath = 'F:\\xampp\\htdocs\\MAIN_php\\bot.py';

    if (file_exists($pythonScriptPath)) {
        $command = escapeshellcmd("python F:\\xampp\\htdocs\\MAIN_php\\bot.py '" . escapeshellarg($question) . "'");
        $output = shell_exec($command . ' 2>&1');

        error_log($output);

        if (empty($output)) {
            echo "Không thể trả lời câu hỏi. Vui lòng thử lại.";
            exit;
        } else {

            echo $output;
            exit;
        }
    } else {
        echo "Lỗi: Script Python không tồn tại.";
        exit;
    }
}



?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        .chat-container {
            position: fixed;
            bottom: 80px;
            right: 20px;
            display: none;
            flex-direction: column;
            align-items: center;
            background: #ffffff;
            width: 350px;
            height: 400px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            padding: 15px;
            z-index: 99;
        }

        .chatbox {
            flex: 1;
            width: 100%;
            overflow-y: auto;
            padding: 10px;
            border-bottom: 1px solid #ddd;
            margin-bottom: 15px;
            z-index: 99;
        }

        .chatbox::-webkit-scrollbar {
            width: 8px;
        }

        .chatbox::-webkit-scrollbar-thumb {
            background-color: #ccc;
            border-radius: 4px;
        }

        .chatbox::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        .message {
            padding: 12px 15px;
            margin: 15px 0;
            border-radius: 20px;
            font-size: 14px;
            max-width: 75%;
            width: fit-content;
            line-height: 1.6;
            word-wrap: break-word;
            display: block;
            text-align: justify;
        }


        .user-message {
            background-color: #d1e7ff;
            text-align: right;
            margin-left: auto;
            margin-right: 0;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            text-align: justify;
        }

        .bot-message {
            background-color: #f8f9fa;
            text-align: left;
            margin-right: auto;
            margin-left: 0;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            text-align: justify;
        }

        .bot-message strong {
            color: #007bff;
        }

        .bot-message em {
            font-style: italic;
            color: #6c757d;
        }

        .bot-message ul {
            margin-left: 20px;
            list-style-type: disc;
        }

        .bot-message li {
            margin-bottom: 5px;
        }

        .input-container {
            display: flex;
            width: 100%;
            max-width: 400px;
        }

        .input-container input {
            flex: 1;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 25px;
            font-size: 16px;
            outline: none;
            transition: 0.3s;
        }

        .input-container input:focus {
            border-color: #007bff;
            box-shadow: 0 0 8px rgba(0, 123, 255, 0.3);
        }

        .btn-send {
            background-color: #007bff;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            margin-left: 10px;
            font-size: 16px;
            transition: background-color 0.3s;
        }

        .btn-send:hover {
            background-color: #0056b3;
        }

        .chat-toggle {
            position: fixed;
            bottom: 20px;
            right: 20px;
            width: 50px;
            height: 50px;
            background-color: #007bff;
            z-index: 99;
            color: white;
            border: none;
            border-radius: 50%;
            font-size: 24px;
            cursor: pointer;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>

<body>

    <div class="header">Chatbot AI - Hỗ trợ kiểm tra đánh giá</div>
        <div class="content">
            <h1>Chào mừng bạn đến với hệ thống chatbot AI!</h1>
            <p>Hãy nhấp vào nút chat ở góc phải để bắt đầu cuộc trò chuyện.</p>
        </div>
    <button class="chat-toggle" onclick="toggleChat()">💬</button>

    <div class="chat-container" id="chat-container">
        <div class="chatbox" id="chatbox"></div>
        <div class="input-container">
            <input type="text" id="user-input" placeholder="Nhập câu hỏi..." onkeydown="checkEnter(event)">
            <button class="btn-send" onclick="sendMessage()">Gửi</button>
        </div>
    </div>

    <script>
        function toggleChat() {
            let chatContainer = document.getElementById('chat-container');
            chatContainer.style.display = chatContainer.style.display === 'flex' ? 'none' : 'flex';
        }


        function sendMessage() {
            let userInput = document.getElementById('user-input').value;
            if (userInput.trim() !== "") {
                displayMessage(userInput, 'user');
                displayMessage('Đang xử lý...', 'bot');
                fetchResponse(userInput);
                document.getElementById('user-input').value = "";
            }
        }

        function checkEnter(event) {
            if (event.key === 'Enter') {
                sendMessage();
            }
        }
        function displayMessage(message, sender) {
            let chatbox = document.getElementById('chatbox');
            let messageElement = document.createElement('div');
            messageElement.classList.add('message');
            messageElement.classList.add(sender === 'user' ? 'user-message' : 'bot-message');

            if (sender === 'bot') {
                let i = 0;
                let displayMessage = '';
                let interval = setInterval(() => {
                    if (message[i] === "\n") {
                        displayMessage += "<br>"; // Thay \n bằng <br> để hiển thị xuống dòng
                    } else {
                        displayMessage += message[i];
                    }

                    messageElement.innerHTML = displayMessage; // Dùng innerHTML thay vì innerText

                    i++;
                    if (i >= message.length) {
                        clearInterval(interval);
                    }
                }, 50);
            } else {
                messageElement.innerHTML = message.replace(/\n/g, '<br>'); // Xử lý xuống dòng ngay lập tức
            }

            chatbox.appendChild(messageElement);
            chatbox.scrollTop = chatbox.scrollHeight;
        }




        async function fetchResponse(question) {
            let response = await fetch('http://localhost:8080/MAIN_php/bot.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: 'question=' + encodeURIComponent(question)
            });

            let data = await response.text();
            let chatbox = document.getElementById('chatbox');
            chatbox.lastChild.remove();
            displayMessage(data, 'bot');
        }
    </script>
</body>

</html>