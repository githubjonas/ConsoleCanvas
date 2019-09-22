# coding: utf8
import inspect, os, sys, math, collections, json

Point = collections.namedtuple('Point', ['x', 'y'])

class ConsoleCanvas:
    COLOR_BLACK = "0;30"
    COLOR_RED = "0;31"
    COLOR_GREEN = "0;32"
    COLOR_BROWN = "0;33"
    COLOR_BLUE = "0;34"
    COLOR_MAGENTA = "0;35"
    COLOR_CYAN = "0;36"
    COLOR_GREY = "0;37"
    COLOR_WHITE = "0"

    TEXT_MODE_LEFT = 0
    TEXT_MODE_CENTER = 1

    BLIT_DEFAULT = 0
    BLIT_MERGE = 1
    BLIT_SAFE = 2

    _CLR_SCREEN = "\033[2J";
    _CHARS = {
        0b0000: " ",
        0b0001: "▗",
        0b0010: "▖",
        0b0011: "▄",
        0b0100: "▝",
        0b0101: "▐",
        0b0110: "▞",
        0b0111: "▟",
        0b1000: "▘",
        0b1001: "▚",
        0b1010: "▌",
        0b1011: "▙",
        0b1100: "▀",
        0b1101: "▜",
        0b1110: "▛",
        0b1111: "█"
    }
    _COLOR_MAP = {
        COLOR_BLACK:    1,
        COLOR_RED:      2,
        COLOR_GREEN:    3,
        COLOR_BROWN:    4,
        COLOR_BLUE:     5,
        COLOR_MAGENTA:  6,
        COLOR_CYAN:     7,
        COLOR_GREY:     8,
        COLOR_WHITE:    9,
    }

    _fonts = {}

    def __init__(self, cols = None, rows = None, output = True):
        self.output = output

        if (cols is None):
            rows, columns = os.popen('stty size', 'r').read().split()
            if (int(columns) < 1):
                self.cols = 80
            else:
                self.cols = int(columns)
        else:
            self.cols = int(cols)

        if (rows is None):
            rows, columns = os.popen('stty size', 'r').read().split()
            if (int(rows) < 1):
                self.rows = 24
            else:
                self.rows = int(rows)
        else:
            self.rows = int(rows)

        self._color = None
        self._cursorX = -1
        self._cursorY = -1

        self._initCanvas()

    def _initCanvas(self):
        self.canvas = [[0 for y in range(self.rows)] for x in range(self.cols)]
        for x in range(0, self.cols):
            for y in range(0, self.rows):
                self.canvas[x][y] =  0

    def _renderPos(self, col, row):
        char = self._CHARS[self.canvas[col][row] & 0b1111]
        if (self.output):
            colorVal = (self.canvas[col][row] & 0b11110000) >> 4
            if (colorVal > 0):
                color = None
                for color in self._COLOR_MAP.items():
                    if color[1] == colorVal:
                        self.setColor(color[0])
            if self._cursorX == col and self._cursorY == row:
                sys.stdout.write(char)
            else:
                sys.stdout.write("\033[" + str(row) + ";" + str(col) + "f" + char)
            self._cursorX = col + 1
            self._cursorY = row

    def _getFont(self, size):
        if size not in self._fonts.keys():
            with open (os.path.dirname(os.path.abspath(inspect.getfile(inspect.currentframe()))) + "/../../font/font_" + str(size) + ".json", "r") as f:
                font = json.load(f)

            self._fonts[size] = font

        return self._fonts[size]

    def clear(self):
        if (self.output):
            sys.stdout.write(self._CLR_SCREEN)

    def blit(self, x, y, canvas, options = {}):
        method = options.setdefault('method', self.BLIT_DEFAULT)
        width = options.setdefault('width', int(canvas.width() / 2))
        height = options.setdefault('height', int(canvas.height() / 2))
        xOffset = options.setdefault('xOffset', 0)
        yOffset = options.setdefault('yOffset', 0)

        xt = int(x / 2)
        yt = int(y / 2)

        canvasData = canvas.canvas

        if (method & self.BLIT_SAFE > 0):
            interCanvas = ConsoleCanvas(width, height, False)
            interCanvas.blit(0, 0, canvas, {'xOffset': xOffset, 'yOffset': yOffset, 'width': width * 2, 'height': height * 2, 'method': method & ~self.BLIT_SAFE})
            canvasData = interCanvas.canvas
            xOffset = 0
            yOffset = 0

        for xp in range(0, width):
            for yp in range(0, height):
                bobx = xp + xOffset
                boby = yp + yOffset
                if bobx >= 0 and bobx < len(canvasData) and boby >= 0 and boby < len(canvasData[bobx]) and xt + xp >= 0 and xt + xp < self.cols and yt + yp >= 0 and yt + yp < self.rows:
                    if (method & self.BLIT_MERGE):
                        self.canvas[xt + xp][yt + yp] = (self.canvas[xt + xp][yt + yp] & 0b1111) | canvasData[bobx][boby]
                    else:
                        self.canvas[xt + xp][yt + yp] = canvasData[bobx][boby]
                    self._renderPos(xt + xp, yt + yp)

    def clone(self):
        clone = ConsoleCanvas(self.cols, self.rows, false)
        clone.canvas = self.canvas

    def getCanvasData(self):
        return self.canvas

    def setCanvasData(self, canvas):
        self.canvas = canvas

    def render (self):
        self.output = True
        for yr in range(0, self.rows):
            for xr in range(0, self.cols):
                self.renderPos(xr, yr)

    def text(self, x, y, text, size = 5, mode = TEXT_MODE_LEFT):
        width = 0
        plotArray = []
        font = self._getFont(size)

        for char in text:
            ascii = str(ord(char))
            if ascii in font.keys():
                j = 0
                while j < len(font[ascii]["data"]):
                    chr = int(font[ascii]["data"][j:j + 2], 16)
                    ix = (chr >> 4) & 0xf
                    iy = chr & 0xf
                    plotArray.append(Point(width + ix + (0 if mode == self.TEXT_MODE_CENTER else x), y + iy))
                    j = j + 2
                width = width + font[ascii]["width"] + 2

        offset = (x - width / 2) if mode == self.TEXT_MODE_CENTER else 0
        for point in plotArray:
            self.plot(point.x + offset, point.y)

        return width - 2

    def writeString(self, xr, yr, string):
        if (self.output is not None):
            self.moveCursor(xr, yr)
            sys.stdout.write(string)

    def circle(self, x, y, r, aspect = 1.0):
        i = 0.0;
        while i < 2 * math.pi:
            self.plot(int(x + math.sin(i) * r * aspect), int(y + math.cos(i) * r))
            i = i + 1 / (float(r) * aspect)

    def setColor(self, color):
        self._color = color
        if (self.output is not None):
            sys.stdout.write("\033[" + color + "m")

    def clearColor(self):
        self._color = None
        if (self.output is not None):
            sys.stdout.write("\033[0m")

    def line(self, x1, y1, x2, y2):
        xd = abs(x2 - x1)
        yd = abs(y2 -y1)
        d = math.ceil(math.sqrt(xd * xd + yd * yd))
        if (d == 0):
            return

        xstep = (x2 - x1) / d
        ystep = (y2 - y1) / d
        cx = x1
        cy = y1
        for i in range(0, int(math.floor(d))):
            self.plot(int(cx), int(cy))
            cx += xstep
            cy += ystep

    def width(self):
        return self.cols * 2

    def height(self):
        return self.rows * 2

    def plot(self, x, y):
        if (x < 0 or y < 0 or x > self.cols * 2 or y > self.rows * 2):
            return

        val = 1
        if (y % 2) == 0:
            val = val << 2
        if (x % 2) == 0:
            val = val << 1

        yr = int(y / 2)
        xr = int(x / 2)

        self.canvas[xr][yr] |= val

        if (self._color != None):
            self.canvas[xr][yr] = (self.canvas[xr][yr] & 0b1111) | (self._COLOR_MAP[self._color] << 4)

        self._renderPos(xr, yr)

    def moveCursor(self, x, y):
        yr = int(y / 2)
        xr = int(x / 2)
        if (self.output):
            sys.stdout.write("\033[" + str(yr) + ";" + str(xr) + "f")
        self._cursorX = xr
        self._cursorY = yr
