async function playGame(gameId) {
    const res = await fetch(FULL_BASE_URL + '/api/customer/play-game.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({game_id: gameId})
    });
    const data = await res.json();
    if (data.success && data.coupon_code) {
        // Store the won coupon code for later use in billing
        localStorage.setItem('dinex_coupon', data.coupon_code);
    }
    return data;
}
