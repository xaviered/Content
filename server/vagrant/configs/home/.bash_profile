# file system
alias ls='ls --color=tty -lh'
alias dudir="du --max-depth=1 -b | awk -F. '{print $1 \"\t\t.\" $2}' | sort -nr"

# searching and filterting
alias efindfile='find . | egrep'
alias findfile='find . | grep'
alias findallhiddenfiles='find . | egrep "/\.."'
alias findhiddenfiles='find . | grep -v "/\.git" | grep -v "/\.idea" | egrep "/\.."'
alias findtext='find . | xargs -l1 grep -Hn'
