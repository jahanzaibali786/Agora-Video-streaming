{{-- <x-app-layout> --}}
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <title>Document</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"
        integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://js.pusher.com/7.0/pusher.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/laravel-echo/dist/echo.iife.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    {{-- @vite('resources/js/app.js') --}}
    <script src="https://cdn.tailwindcss.com?plugins=forms,typography,aspect-ratio,line-clamp,container-queries"></script>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">

    <style>
        /* Custom styles */
        .custom-scrollbar::-webkit-scrollbar {
            width: 8px;
            border-radius:10px; 
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: #9c9c9c;
            border-radius: 50px !important;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background-color: #fff;
            border: 2px solid #9c9c9c;
            border-radius:50px !important; 
        }

        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background-color: #555;
        }

        .custom-scrollbar {
            scrollbar-width: thin;
            scrollbar-color: #fff #9c9c9c;
            /* border-radius:50px !important;  */
        }

        #messages {
            max-width: 100%;
            display: flex;
            flex-direction: column;
            row-gap: 10px;
        }

        .left .group-item {
            cursor: pointer;
        }

        /* Common styles for both sender and receiver messages */
        #messages .message {
            padding: 8px 12px;
            border-radius: 15px;
            margin-bottom: 10px;
            color: #000;
            max-width: 60%;
            word-wrap: break-word;
        }

        #messages .sender {
            background-color: #d3d3d3;
            align-self: flex-end;
        }

        #messages .receiver {
            background-color: #4285f4;
            color: white;
            align-self: flex-start;
        }

        .badge {
            background: blue;
            color: #fff;
            padding: 0px 5px;
            border-radius: 50px;
            width: 20px;
            height: 23px;
        }

        .no-message {
            color: #fff;
        }
        .user-name{
            font-size:0.6rem;
            font-style: italic;
        }
    </style>
</head>
<body class="bg-slate-950">
    <h2 class="text-center text-white font-bold pt-4">Group Messenger</h2>
    {{-- @dd($groups,$groupusers) --}}
    @if ($groups->isNotEmpty() && $groupusers->isNotEmpty())
        <div class="mt-2 pt-3">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="grid" style="display: grid; grid-template-columns: 30% 68%; gap: 10px;">
                    <div class="left bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="px-3 pt-4 text-white" style="font-weight: 500;">All Chats</div>
                        <div class="p-2 custom-scrollbar" style="height: 70vh; overflow-y: auto;">
                            <div style="display: flex; flex-direction: column; row-gap: 10px;">
                                @php
                                    $displayedGroupIds = [];
                                @endphp

                                @foreach ($groups as $group)
                                    @php
                                        $displayedGroupIds[] = $group->id;
                                    @endphp

                                    <div class="flex gap-4 items-center bg-white dark:bg-gray-600 p-2 shadow-sm sm:rounded-lg group-item"
                                        data-group-id="{{ $group->id }}" data-group-name="{{ $group->name }}"
                                        data-group-img="{{ asset('storage/' . $group->profile_image) }}">
                                        <div class="img" style="width: 40px; height: 40px;">
                                            <img src="{{ asset('storage/' . $group->profile_image) }}" alt="avatar"
                                                style="border-radius: 50px; height:100%; width:100%; object-fit:cover;">
                                        </div>
                                        <span class="text-white">{{ $group->name }}</span>
                                        <span class="badge hidden">0</span>
                                    </div>
                                @endforeach

                                @foreach ($groupusers as $groupuser)
                                    @foreach ($groupuser->groups as $group)
                                        @php
                                            if (in_array($group->id, $displayedGroupIds)) {
                                                continue;
                                            }
                                            $displayedGroupIds[] = $group->id;
                                        @endphp

                                        <div class="flex gap-4 items-center bg-white dark:bg-gray-600 p-2 shadow-sm sm:rounded-lg group-item"
                                            data-group-id="{{ $group->id }}" data-group-name="{{ $group->name }}"
                                            data-group-img="{{ asset('storage/' . $group->profile_image) }}">
                                            <div class="img" style="width: 40px; height: 40px;">
                                                <img src="{{ asset('storage/' . $group->profile_image) }}"
                                                    alt="avatar"
                                                    style="border-radius: 50px; height:100%; width:100%; object-fit:cover;">
                                            </div>
                                            <span class="text-white">{{ $group->name }}</span>
                                            @if ($groupuser->seenMessages->count() > 0)
                                                <span class="badge">{{ $groupuser->seenMessages->count() }}</span>
                                            @else
                                                <span class="badge hidden">0</span>
                                            @endif
                                        </div>
                                    @endforeach
                                @endforeach

                            </div>
                        </div>
                    </div>
                    <div class="right p-2 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div id="chatHeader"
                            class="p-2 flex justify-between bg-white dark:bg-gray-700 shadow-sm sm:rounded-lg">
                            <div class="header flex gap-4 items-center">
                                <div class="img" style="width: 40px; height: 40px; object-fit:cover;">
                                    <img id="chatGroupprofile" src="{{ asset('storage/users-avatar/avatar.png') }}"
                                        alt="avatar"
                                        style="border-radius: 50px;  object-fit:cover; height:100%; width:100%;">
                                </div>
                                <span hidden id="groupIdd"></span>
                                <span id="chatGroupName" class="dark:text-gray-400">Select a group to chat</span>
                            </div>
                            <div class="flex dark:text-gray-400 gap-4 items-center justify-end">
                                <button id="addUserIcon" data-group-id="" disabled>
                                    <i class="fa-solid fa-user-plus"></i>
                                </button>
                            </div>
                        </div>
                        <div id="chat-box"
                            class="custom-scrollbar chat p-2 mt-2 bg-white dark:bg-gray-700 shadow-sm sm:rounded-lg"
                            style="height: 60vh; overflow-y: auto;">
                            <div id="messages">
                            </div>
                        </div>
                        <div id="messageInputContainer"
                            class="mt-2 p-2 bg-white dark:bg-gray-700 shadow-sm sm:rounded-lg"
                            style="display: grid; grid-template-columns: 10% 80% 10%" data-group-id="">
                            <button id="sendFileIcon" class=" bg-white shadow-sm ms-2" style="border-radius: 50%; width:50px; height:50px; " data-group-id="" disabled>
                                <i class="fa-solid fa-paperclip"></i>
                            </button>
                            <input type="text" id="messageInput" class="w-100 p-2 rounded-lg border"
                                placeholder="Type your message here..." disabled>
                            <button id="sendMessageButton"
                                class="ml-2 bg-blue-500 text-white font-bold py-2 px-4 rounded" disabled>
                                <i class="fa-regular fa-paper-plane"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="flex justify-center items-center h-screen">
            <div class="flex items-center justify-center text-gray-800 dark:text-gray-200 text-5xl">
                No groups found.
            </div>
        </div>
    @endif
    <!-- Add User Modal -->
    <div id="addUserModal" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black bg-opacity-50">
        <div class="bg-white text-white dark:bg-gray-800 p-4 rounded-lg shadow-lg max-w-md w-full">
            <div class="flex justify-between items-center mb-2">
                <h5 class="modal-title" id="addUserModalLabel">Add User to Group</h5>
                <button class="closeuserModal float-right" style="font-weight: bold;">&times;</button>
            </div>
            <div id="usermodalContent">
                <input type="hidden" id="groupIdInput">
                <div id="userList" style="row-gap: 5px;display: flex;flex-direction: column;">
                </div>
            </div>
        </div>
    </div>
    <div id="uploadFileModal"
        class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black bg-opacity-50">
        <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow-lg max-w-md w-full">
            <div class="flex justify-between items-center">
                <h5 class="modal-title text-white">Upload File</h5>
                <button class="text-white closeModal">&times;</button>
            </div>
            <div class="mt-4">
                <form id="uploadFileForm" enctype="multipart/form-data">
                    <div class="border-dashed border-4 border-gray-300 p-4 rounded-lg text-center"
                        ondragover="event.preventDefault()" ondrop="handleFileDrop(event)">
                        <p class="text-white">Drag & drop files or click to select one</p>
                        <input type="file" id="fileInput" class="hidden" name="files[]" multiple>
                        <div class="mt-2">
                            <button type="button" id="fileSelectButton"
                                class="text-blue-600 py-2 px-4 rounded">Select Files</button>
                        </div>
                        <div class="mt-2" id="filenames"></div>
                    </div>
                </form>
            </div>
            <div class="mt-4">
                <button id="sendFileButton" class="bg-blue-500 text-white py-2 px-4 rounded float-right"
                    disabled>Send</button>
            </div>
        </div>
    </div>

    {{-- pusher configuration --}}
    <script>
        window.Pusher = Pusher;
        window.Echo = new Echo({
            broadcaster: 'pusher',
            key: '{{ env('PUSHER_APP_KEY') }}',
            cluster: '{{ env('PUSHER_APP_CLUSTER') }}',
            forceTLS: true,
            encrypted: true,
        });
    </script>
    {{-- Run time get pusher message  --}}
    <script>
        const groupIds = @json($displayedGroupIds);
        var pusher = new Pusher('638f520d4f6c9a0a52d8', {
            cluster: 'ap2'
        });
        if (pusher) {
            groupIds.forEach(groupId => {
                var channel = pusher.subscribe('group-chat.' + groupId);
                channel.bind('group-chat', (e) => {
                    opendchat(e.group_id, e.from_id);
                });
                var fileChannel = pusher.subscribe('group-files.' + groupId);
                fileChannel.bind('group-files', function(data) {
                    var messageDiv = document.createElement('div');
                    messageDiv.classList.add('message');
                    var img = document.createElement('img');
                    img.src = `{{ asset('storage/${data.file}') }}`;
                    img.alt = 'Uploaded file';
                    img.style.maxWidth = '200px';
                    img.style.borderRadius = '20px';
                    var userInfo = document.createElement('p');
                    userInfo.style.fontSize = '0.7rem';
                    userInfo.textContent = `Sent By: ${data.from_id}`;
                    if (data.from_id == {{ auth()->user()->id }}) {
                        messageDiv.classList.add('sender');
                    } else {
                        messageDiv.classList.add('receiver');
                    }
                    messageDiv.appendChild(img);
                    messageDiv.appendChild(userInfo);
                    document.getElementById('messages').appendChild(messageDiv);
                });
            });
        }
    </script>
    {{-- pusher configuration --}}
    <script>
        let currentGroupId = null;
        const unreadCounts = {};

        function scrollToBottom() {
            const chatBox = document.getElementById("chat-box");
            chatBox.scrollTop = chatBox.scrollHeight;
        }

        function opendchat(runtimegroupId, senderId) {
            let opendgroupId = document.getElementById('groupIdd').textContent ?? null;
            if (opendgroupId && opendgroupId == runtimegroupId) {
                const url = `{{ route('seen_messages', ':groupId') }}`.replace(':groupId', opendgroupId);
                fetch(url)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        const activeChatBadge = document.querySelector(
                            `.left .group-item[data-group-id="${opendgroupId}"] .badge`);
                        if (activeChatBadge) {
                            activeChatBadge.classList.add('hidden');
                            activeChatBadge.textContent = '';
                        }
                    })
                    .catch(error => {
                        console.error('Error loading messages:', error);
                    });
            }
        }

        function updateBadge(groupId, fromuserId = null) {
            const badge = document.querySelector(`.group-item[data-group-id="${groupId}"] .badge`);

            if (badge) {
                if (fromuserId != null && fromuserId != {{ auth()->user()->id }}) {
                    unreadCounts[groupId] = (unreadCounts[groupId] || 0) + 1;
                    badge.textContent = unreadCounts[groupId];
                    badge.classList.remove('hidden');
                } else {
                    badge.classList.add('hidden');
                }
            } else {
                console.error('Group item with id' + groupId + 'not found');
            }
        }

        // Function to handle subscribing to all group chats
        function subscribeToGroups() {
            if (pusher) {
                groupIds.forEach(groupId => {
                    var channel = pusher.subscribe('group-chat.' + groupId);
                    channel.bind('group-chat', (e) => {
                        const messagesContainer = document.getElementById('messages');
                        const messageElement = document.createElement('p');
                        messageElement.classList.add('message');

                        if (e.from_id == {{ auth()->user()->id }}) {
                            messageElement.classList.add('sender');
                        } else {
                            messageElement.classList.add('receiver');
                        }
                        messageElement.textContent = e.message;
                        if (currentGroupId == groupId) {
                            messagesContainer.appendChild(messageElement);
                            scrollToBottom();
                        } else {
                            updateBadge(groupId, e.from_id);
                        }
                    });
                });
            }
        }

        document.querySelectorAll('.group-item').forEach(group => {
            group.addEventListener('click', function() {
                const groupId = this.dataset.groupId;
                const groupName = this.dataset.groupName;
                const groupImg = this.dataset.groupImg;

                // Update header and enable input fields
                document.getElementById('chatGroupName').textContent = groupName;
                document.getElementById('chatGroupName').style.color = '#fff';
                document.getElementById('groupIdd').textContent = groupId;
                document.getElementById('chatGroupprofile').src = groupImg;
                document.getElementById('messageInputContainer').dataset.groupId = groupId;
                document.getElementById('messageInput').disabled = false;
                document.getElementById('sendMessageButton').disabled = false;
                document.getElementById('sendFileIcon').disabled = false;
                const addUserIcon = document.getElementById('addUserIcon');
                addUserIcon.disabled = false;
                addUserIcon.style.color = 'white';
                addUserIcon.setAttribute('data-group-id', groupId);

                // Clear existing messages
                const messagesContainer = document.getElementById('messages');
                messagesContainer.innerHTML = '';
                fetch(`{{ route('fetch_messages', '') }}/${groupId}`)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.length == 0) {
                            const noMessageElement = document.createElement('div');
                            noMessageElement.textContent = "No messages";
                            noMessageElement.classList.add('no-message');
                            noMessageElement.style.textAlign = "center";
                            noMessageElement.style.marginTop = "20px";
                            messagesContainer.appendChild(noMessageElement);
                        } else {
                            data.forEach(item => {
                                const messageElement = document.createElement('div');
                                messageElement.classList.add('message');
                                const userNameElement = document.createElement('p');
                                userNameElement.classList.add('user-name');
                                userNameElement.textContent = `${item.user_name}`;
                                if (item.type == 'body') {
                                    const messageText = document.createElement('p');
                                    messageText.textContent = item.content;
                                    messageElement.appendChild(messageText);
                                    messageElement.appendChild(userNameElement);
                                } else if (item.type == 'file_path') {
                                    const fileElement = document.createElement('img');
                                    fileElement.src = `{{ asset('storage/${item.content}') }}`;
                                    fileElement.alt = 'Uploaded file';
                                    fileElement.style.maxWidth = '200px';
                                    fileElement.style.borderRadius = '20px';

                                    messageElement.appendChild(fileElement);
                                    messageElement.appendChild(userNameElement);
                                }
                                if (item.from_id == {{ auth()->user()->id }}) {
                                    messageElement.classList.add('sender');
                                } else {
                                    messageElement.classList.add('receiver');
                                }

                                messagesContainer.appendChild(messageElement);
                            });
                            scrollToBottom();
                        }
                        updateBadge(groupId);
                    })
                    .catch(error => {
                        console.error('Error loading messages:', error);
                    });

                currentGroupId = groupId;


            });
        });

        // Send message logic
        document.getElementById('sendMessageButton').addEventListener('click', sendMessage);
        document.getElementById('messageInput').addEventListener('keydown', function(event) {
            if (event.key == 'Enter') {
                event.preventDefault();
                sendMessage();
            }
        });

        // Function to send the message
        function sendMessage() {
            const messageInput = document.getElementById('messageInput');
            const message = messageInput.value.trim();
            const groupId = document.getElementById('messageInputContainer').dataset.groupId;
            const messagesContainer = document.getElementById('messages');
            if (message && groupId) {
                fetch(`{{ route('store_messages', '') }}/${groupId}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            content: message
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        messageInput.value = '';
                        const noMessageElement = document.querySelector('.no-message');
                        if (noMessageElement) {
                            noMessageElement.remove();
                        }
                        // const messageElement = document.createElement('p');
                        // messageElement.classList.add('message', 'sender');
                        // messageElement.textContent = message;
                        // messagesContainer.appendChild(messageElement);
                        scrollToBottom();
                    })
                    .catch(error => {
                        console.error('Error sending message:', error);
                    });
            }
        }

        // Create group modal
        document.getElementById('createGroupButton').addEventListener('click', function() {
            fetch(`{{ route('create_group') }}`)
                .then(response => response.text())
                .then(data => {
                    document.getElementById('modalContent').innerHTML = data;
                    document.getElementById('createGroupModal').classList.remove('hidden');
                })
                .catch(error => {
                    console.error('Error fetching create group form:', error);
                });
        });

        // Close modal
        document.querySelector('.closeModal').addEventListener('click', function() {
            document.getElementById('createGroupModal').classList.add('hidden');
        });

        // Initialize message listener for all groups
        document.addEventListener('DOMContentLoaded', () => {
            subscribeToGroups(); // Subscribe to all groups' chat messages
        });
    </script>
    {{-- // Add Users In group --}}
    <script>
        document.getElementById('addUserIcon').addEventListener('click', function() {
            const groupId = this.getAttribute('data-group-id');
            if (groupId) {
                document.getElementById('groupIdInput').value = groupId;
                fetch(`{{ route('fetch_users', ':groupId') }}`.replace(':groupId', groupId))
                    .then(response => response.json())
                    .then(data => {
                        const userList = document.getElementById('userList');
                        userList.innerHTML = '';
                        if (data.length === 0) {
                            userList.innerHTML = '<p>No users available to add</p>';
                        } else {
                            data.forEach(user => {
                                const userItem = document.createElement('div');
                                userItem.classList.add('user-item');
                                userItem.innerHTML = `
                                    <div class="grid items-center dark:bg-gray-700 shadow-sm sm:rounded-lg p-2" style="grid-template-columns:50% 50%;">
                                        <div>
                                            <span>${user.name}</span>
                                        </div>
                                        <div class="float-right flex justify-end">
                                            <button class="btn px-4 text-white dark:bg-blue-500 btn-sm sm:rounded-lg add-user-btn" data-user-id="${user.id}" data-group-id="${groupId}">
                                                Add
                                            </button>
                                        </div>
                                    <div>
                                `;
                                userList.appendChild(userItem);
                            });
                        }

                        document.querySelectorAll('.add-user-btn').forEach(button => {
                            button.addEventListener('click', function() {
                                const userId = this.getAttribute('data-user-id');
                                const groupId = this.getAttribute('data-group-id');
                                addUserToGroup(userId, groupId);
                            });
                        });
                    })
                    .catch(error => {
                        console.error('Error fetching users:', error);
                    });
                document.getElementById('addUserModal').classList.remove('hidden');
            }
        });

        document.querySelector('.closeuserModal').addEventListener('click', function() {
            document.getElementById('addUserModal').classList.add('hidden');
        });
        document.getElementById('addUserModal').addEventListener('click', function(event) {
            const modalContent = document.querySelector('#addUserModal > div');
            if (!modalContent.contains(event.target)) {
                document.getElementById('addUserModal').classList.add('hidden');
            }
        });

        function addUserToGroup(userId, groupId) {
            fetch(`{{ route('add_user_to_group', '') }}/${groupId}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        user_id: userId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        console.log('User added to group successfully');
                        document.querySelector(`button[data-user-id="${userId}"]`).parentElement.remove();
                    } else {
                        alert(data.message || 'Error: Could not add user to the group');
                        document.getElementById('addUserModal').classList.add('hidden');
                    }
                })
                .catch(error => {
                    console.error('Error adding user to group:', error);
                    alert('An error occurred while adding the user to the group.');
                });
        }
    </script>
    <script>
        const fileInput = document.getElementById('fileInput');
        const filenamesContainer = document.getElementById('filenames');

        let selectedFiles = [];

        document.getElementById('sendFileIcon').addEventListener('click', function() {
            const groupId = document.getElementById('messageInputContainer').dataset.groupId;
            if (groupId) {
                document.getElementById('uploadFileModal').classList.remove('hidden');
                document.getElementById('sendFileButton').dataset.groupId = groupId;
            }
        });

        document.querySelectorAll('.closeModal').forEach(button => {
            button.addEventListener('click', function() {
                document.getElementById('uploadFileModal').classList.add('hidden');
            });
        });

        window.addEventListener('click', function(event) {
            if (event.target.id == 'uploadFileModal') {
                document.getElementById('uploadFileModal').classList.add('hidden');
            }
        });

        document.getElementById('fileSelectButton').addEventListener('click', () => {
            fileInput.click();
        });

        fileInput.addEventListener('change', (event) => {

            const files = Array.from(event.target.files);
            // console.log(files);
            selectedFiles = [...selectedFiles, ...files];
            updateFilenames();
            toggleSendButton();
        });

        function handleFileDrop(event) {
            event.preventDefault();
            const files = Array.from(event.dataTransfer.files);
            selectedFiles = [...selectedFiles, ...files];
            updateFilenames();
            toggleSendButton();
        }

        function updateFilenames() {
            filenamesContainer.innerHTML = '';
            selectedFiles.forEach((file) => {
                const fileElement = document.createElement('div');
                fileElement.textContent = file.name;
                filenamesContainer.appendChild(fileElement);
            });
        }

        function toggleSendButton() {
            const sendFileButton = document.getElementById('sendFileButton');
            sendFileButton.disabled = selectedFiles.length == 0;
        }

        document.getElementById('sendFileButton').addEventListener('click', () => {
            const formData = new FormData();
            const groupId = document.getElementById('messageInputContainer').dataset.groupId;
            selectedFiles.forEach(file => {
                formData.append('files[]', file, selectedFiles);
            });

            console.log(groupId, formData);

            fetch(`{{ route('send_file', '') }}/${groupId}`, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    console.log(data);
                    selectedFiles = [];
                    updateFilenames();
                    document.getElementById('uploadFileModal').classList.add('hidden');
                })
                .catch(error => console.error('Error uploading files:', error));
        });
    </script>

{{-- </x-app-layout> --}}
</body>
</html>