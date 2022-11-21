function get_angle(text, profile) {
    var digest = sha1.digest(text);
    let angle = (((digest[0] & 0xFF) + ((digest[1]) & 0xFF) * 256) / 65536.0) * 360;

    switch (profile) {
        case "red-green":
            return mod(((angle + 90) % 180) - 90, 360);
        case "blue":
            return angle % 180;
        default:
    }
    return angle;
}

function rgbToHex(rgb) {
    return "#" + (
        (1 << 24) +
        (rgb[0] << 16) +
        (rgb[1] << 8) +
        rgb[2]
    ).toString(16).slice(1);
}

function hexToRgb(hex) {
    var bigint = parseInt(hex.substr(1), 16);
    var r = (bigint >> 16) & 255;
    var g = (bigint >> 8) & 255;
    var b = bigint & 255;

    return [r, g, b];
}

function updateMapping(mapping, new_entry) {
    // new_entry = [ Angle, L, R, G, B ]
    for (curr_entry of mapping) {
        if (curr_entry[0] == new_entry[0]) {
            if (Math.abs(73.2 - curr_entry[1]) > Math.abs(73.2 - new_entry[1])) {
                curr_entry[0] = new_entry[0];
                curr_entry[1] = new_entry[1];
                curr_entry[2] = new_entry[2];
                curr_entry[3] = new_entry[3];
                curr_entry[4] = new_entry[4];
                return;
            }
        }
    }
    mapping.push(new_entry);
}

function get_hue_palette(colors_rgb) {
    let mapping = [
        // [ Angle, L, R, G, B ]
    ];
    for (rgb_color of colors_rgb) {
        if (rgb_color[0] == rgb_color[1] && rgb_color[1] == rgb_color[2]) {
            continue;
        }
        let hsl = hsluv.rgbToHsluv(rgb_color);
        let rounded_angle = Math.round(hsl[0] + 0.5);
        updateMapping(mapping, [rounded_angle, hsl[2], rgb_color[0], rgb_color[1], rgb_color[2]]);
    }
    mapping = mapping.map(function (entry) {
        return [entry[0], entry[2], entry[3], entry[4]];
    });
    return mapping;
}

function mod(x, y) {
    return ((x % y) + y) % y;
}

function select_from_palette(palette, alpha) {
    let p = [...palette];
    p.sort(function (a, b) {
        let a_beta = a[0];
        let a_D = Math.min(mod(alpha - a_beta, 360), mod(a_beta - alpha, 360));

        let b_beta = b[0];
        let b_D = Math.min(mod(alpha - b_beta, 360), mod(b_beta - alpha, 360));

        return a_D - b_D;
    });
    return [p[0][1], p[0][2], p[0][3]];
}

/*
function update() {
	console.log("updating");
	let text = document.getElementById("text").value;
	let profile = document.getElementById("profile").value;
	let angle = get_angle(text, profile);
	document.getElementById("angle").innerText = angle;

	let rgb = hsluv.hsluvToRgb([angle, 100, 50]);
	let rgbhex = rgbToHex(rgb.map((x)=>Math.round(x*255)));
	document.getElementById("rgbhex").innerText = rgbhex;
	document.getElementById("rgb-sample").style.backgroundColor = rgbhex;

	let colors_text = document.getElementById("palette").value.split("\n");
	let colors_rgb = colors_text.map(hexToRgb);

	let pal_el = document.getElementById("palette-visual");
	pal_el.innerHTML = "";
	for (palette_hex of colors_text) {
		let pal_entry = document.createElement("div");
		pal_entry.className = "color-sample";
		pal_entry.style.backgroundColor = palette_hex;
		pal_el.appendChild(pal_entry);
	}

	let rgb_palette = rgbToHex(select_from_palette(get_hue_palette(colors_rgb), angle));
	console.log(rgb_palette);
	document.getElementById("rgbhex-palette").innerText = rgb_palette;
	document.getElementById("rgb-sample-palette").style.backgroundColor = rgb_palette;
}

window.onload = function () {
	console.log("loaded");
	var hash = window.location.hash.substr(1);
	if(hash.length > 0) {
		document.getElementById("text").value = hash;
	}
	update();
}
*/

function colourize(text) {
    let angle = get_angle(text, null);
    let rgb = hsluv.hsluvToRgb([angle, 100, 50]);
    let rgbhex = rgbToHex(rgb.map((x) => Math.round(x * 255)));
    return rgbhex;
}
