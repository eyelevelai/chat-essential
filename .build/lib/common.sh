must_have() {
    for cmd in $*; do
        command -v $cmd >/dev/null 2>&1 || { error "$cmd command is required but not installed"; exit 1; }
    done
}

error() { echo -e >&2 "$@ \e[31;1m\u2717\e[0m"; }

status() { echo -ne "\e[37;1m$@: \e[0m"; }

ok() {
    [[ "$@" != "" ]] && echo -n "$@ "
    echo -e "\e[32;1m\u2713\e[0m"
}

warn() { echo -e "$@ \e[33;1m\u26a0\e[0m"; }
