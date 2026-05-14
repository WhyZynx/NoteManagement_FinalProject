<div id="shareModal" style="
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.25);
    backdrop-filter: blur(5px);
    z-index: 18000;
    align-items: center;
    justify-content: center;
    font-family: 'Montserrat', sans-serif;
">
    <div style="
        width: 90%;
        max-width: 360px;
        background: #fff;
        padding: 24px;
        border-radius: 18px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        display: flex;
        flex-direction: column;
        gap: 14px;
    ">
        <h3 style="
            margin: 0 0 6px;
            font-size: 20px;
            color: #222;
        ">
            Share Note
        </h3>

        <input 
            type="email" 
            id="shareEmail" 
            placeholder="Enter email"
            style="
                padding: 12px;
                border: 1px solid #ddd;
                border-radius: 12px;
                outline: none;
                font-size: 14px;
            "
        >

        <select 
            id="sharePermission"
            style="
                padding: 12px;
                border: 1px solid #ddd;
                border-radius: 12px;
                outline: none;
                font-size: 14px;
                background: #fff;
                cursor: pointer;
            "
        >
            <option value="read" selected>Read Only</option>
            <option value="edit">Can Edit</option>
        </select>

        <div id="sharedUsersSection" style="
            margin-top: 5px;
            padding-top: 15px;
            border-top: 1px solid #eee;
        ">
            <h4 style="
                margin: 0 0 12px;
                font-size: 12px;
                font-weight: 600;
                color: #555;
            ">
                People with access
            </h4>
            
            <div id="sharedUsersList" style="
                display: flex;
                flex-direction: column;
                gap: 2px;
                max-height: 180px;
                overflow-y: auto;
            ">
                <div style="
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                    padding: 8px 0;
                ">
                    <span style="font-size: 13px; color: #555;">Loading...</span>
                </div>
            </div>
        </div>

        <div style="
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 6px;
        ">
            <button 
                onclick="closeShareModal()"
                style="
                    padding: 10px 16px;
                    border: none;
                    border-radius: 10px;
                    background: #f3f4f6;
                    cursor: pointer;
                    font-weight: 500;
                "
            >
                Cancel
            </button>

            <button 
                id="confirmShare"
                style="
                    padding: 10px 18px;
                    border: none;
                    border-radius: 10px;
                    background: #5385c7;
                    color: white;
                    cursor: pointer;
                    font-weight: 600;
                "
            >
                Share
            </button>
        </div>
    </div>
</div>