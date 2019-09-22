from GII.ConsoleCanvas import ConsoleCanvas

canvas = ConsoleCanvas()
canvas.clear()
canvas.setColor(canvas.COLOR_GREEN)
canvas.line(10,6,46,11)
canvas.setColor(canvas.COLOR_BLUE)
canvas.circle(50,20,15,1.5)
canvas.setColor(canvas.COLOR_MAGENTA)
canvas.text(11,5,"Hello peepz")
canvas.moveCursor(0, 40)
canvas.clearColor()
